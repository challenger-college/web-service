<?php

namespace App\Service;

use App\Entity\Challenge;
use App\Entity\Input;
use App\Entity\Output;
use App\Entity\Test;
use App\Repository\ChallengeRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

class ChallengeService
{
    public function __construct(
        Security $security,
        RequestStack $requestStack,
        ChallengeRepository $challengeRepository,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        ParameterBagInterface $parameterBag
    ) {
        $this->challengeRepository = $challengeRepository;
        $this->em = $em;
        $this->request = $requestStack->getMainRequest();
        $this->user = $security->getUser();
        $this->slugger = $slugger;
        $this->parameterBag = $parameterBag;
    }

    public function create(): Challenge
    {
        $challenge = new Challenge();
        $challenge = $this->extractToChallenge($this->request, $challenge);
        $challenge = $this->persist($challenge);

        return $challenge;
    }

    public function update(string $challenge_id): Challenge
    {
        $challenge = $this->challengeRepository->find($challenge_id);
        $challenge = $this->extractToChallenge($this->request, $challenge);
        $challenge = $this->persist($challenge);

        return $challenge;
    }

    protected function persist(Challenge $challenge): Challenge
    {
        $this->em->persist($challenge);
        $this->em->flush();

        return $challenge;
    }

    protected function extractToChallenge(Request $request, Challenge $challenge): Challenge
    {
        $challenge->setTitle($request->get('title'));
        $challenge->setDescription($request->get('description'));
        $challenge->setConstraints($request->get('constraints'));
        $challenge->setTimeout($request->get('timeout'));
        $challenge->setFunctionName($request->get('function_name'));
        $challenge->setValidity(null);

        foreach ($request->files ?? [] as $file) {
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
                $file->move(
                    $this->parameterBag->getParameter('images_directory'),
                    $newFilename
                );
                $challenge->setImage($newFilename);
            }
        }

        // Get tests submit in form
        foreach ($request->get('inputs_value') ?? [] as $index => $values) {
            foreach ($values as $key => $value) {
                if ('' !== $value) {
                    $tests[$index]['inputs'][$key]['value'] = $value;
                }
            }
        }
        foreach ($request->get('inputs_name') ?? [] as $index => $names) {
            foreach ($names as $key => $name) {
                if ('' !== $name) {
                    $tests[$index]['inputs'][$key]['name'] = $name;
                }
            }
        }
        foreach ($request->get('output') ?? [] as $index => $name) {
            if ('' !== $name) {
                $tests[$index]['output'] = $name;
            }
        }

        // Clear old tests
        foreach ($challenge->getTests() ?? [] as $test) {
            $challenge->removeTest($test);
        }

        // Construct Test object with Inputs and Output
        foreach ($tests ?? [] as $test) {
            $Test = new Test();

            $output = new Output();
            $output->setValue($test['output']);
            $output->setTest($Test);
            // $this->em->persist($output);

            foreach ($test['inputs'] as $input) {
                $Input = new Input();
                $Input->setName($input['name']);
                $Input->setValue($input['value']);
                $Input->setTest($Test);
                // $this->em->persist($Input);
            }

            $challenge->addTest($Test);
            // $this->em->persist($Test);
        }

        $challenge->setAuthor($this->user);
        $challenge->setUpdateDate(new DateTime());

        return $challenge;
    }
}

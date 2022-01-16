<?php

namespace App\Controller;

use DateTime;
use App\Entity\Test;
use App\Entity\Input;
use App\Entity\Output;
use App\Entity\Challenge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

class ChallengeController extends AbstractController
{
    #[Route('/challenges', name: 'challenges')]
    public function challenges(EntityManagerInterface $em): Response
    {
        $challenges = $em->getRepository(Challenge::class)->findBy(['validity' => true], ['createDate' => 'DESC']);
        return $this->render('challenge/challenges.html.twig', ['challenges' => $challenges]);
    }

    #[Route('/challenge/{challenge_id}', name: 'challenge')]
    public function challenge(Request $request, ?string $challenge_id = null, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($challenge_id):
            $challenge = $em->getRepository(Challenge::class)->find($challenge_id);
        else: $challenge = new Challenge(); endif;

        if ($request->isMethod('POST')):
            $challenge->setTitle($request->get('title'));
            $challenge->setDescription($request->get('description'));
            $challenge->setConstraints($request->get('constraints'));
            $challenge->setTimeout($request->get('timeout'));
            $challenge->setFunctionName($request->get('function_name'));
            $challenge->setValidity(false);
            
            foreach ($request->files ?? [] as $file):
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
                $file->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $challenge->setImage($newFilename);
            endforeach;
            
            // Get tests submit in form
            foreach ($request->get('inputs_value') ?? [] as $index => $values):
                foreach ($values as $key => $value):
                    if ($value !== ""):
                        $tests[$index]['inputs'][$key]['value'] = $value;
                    endif;
                endforeach;
            endforeach;
            foreach ($request->get('inputs_name') ?? [] as $index => $names):
                foreach ($names as $key => $name):
                    if ($name !== ""):
                        $tests[$index]['inputs'][$key]['name'] = $name;
                    endif;
                endforeach;
            endforeach;
            foreach ($request->get('output') ?? [] as $index => $name):
                    if ($name !== ""):
                        $tests[$index]['output'] = $name;
                    endif;
            endforeach;

            // Clear old tests
            foreach ($challenge->getTests() ?? [] as $test):
                $challenge->removeTest($test);
            endforeach;

            // Construct Test object with Inputs and Output
            foreach ($tests ?? [] as $test):
                $Test = new Test();

                $output = new Output();
                $output->setValue($test['output']);
                $output->setTest($Test);
                $em->persist($output);

                foreach ($test['inputs'] as $input):
                    $Input = new Input();
                    $Input->setName($input['name']);
                    $Input->setValue($input['value']);
                    $Input->setTest($Test);
                    $em->persist($Input);
                endforeach;

                $challenge->addTest($Test);
                $em->persist($Test);
            endforeach;

            $challenge->setAuthor($this->getUser());
            $challenge->setUpdateDate(new DateTime());
            $em->persist($challenge);
            $em->flush();

            return $this->redirectToRoute('challenges');
        endif;

        return $this->render('challenge/challenge.html.twig', ['challenge' => $challenge]);
    }
}

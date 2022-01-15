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
use Symfony\Component\HttpFoundation\JsonResponse;

class ChallengeController extends AbstractController
{
    #[Route('/challenge', name: 'challenge')]
    public function challenge(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')):
            $challenge = new Challenge();
            $challenge->setTitle($request->get('title'));
            $challenge->setDescription($request->get('description'));
            $challenge->setConstraints($request->get('constraints'));
            $challenge->setTimeout($request->get('timeout'));
            $challenge->setFunctionName($request->get('function_name'));
            
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
            $challenge->setCreateDate(new DateTime());
            $challenge->setUpdateDate(new DateTime());
            $em->persist($challenge);
            $em->flush();

            return $this->json(['status' => 'success']);
        endif;

        return $this->json(['error' => 'Formulaire invalide.']);
    }
}

<?php

namespace App\Controller;

use App\Entity\Challenge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    #[Route('/api/challenges', name: 'api_challenges')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->get('token') === $this->getParameter('token.api')):
            foreach ($em->getRepository(Challenge::class)->findAll() ?? [] as $challenge):
                foreach ($challenge->getTests() ?? [] as $test):
                    foreach ($test->getInputs() as $input):
                        $inputs[] = ['name' => $input->getName(), 'value' => $input->getValue()];
                    endforeach;
                    $tests[] = ['inputs' => $inputs, 'output' => $test->getOutput()->getValue()];
                endforeach;
                
                $challenges[] = [
                    'function_name' => $challenge->getFunctionName(),
                    'timeout' => $challenge->getTimeout(),
                    'tests' => $tests
                ];
            endforeach;

            return $this->json([
                'status' => 'success',
                'challenges' => $challenges ?? []
            ]);
        endif;

        return $this->json(['status' => 'error', 'message' => 'Fake token.']);
    }

    #[Route('/api/challenge/check', name: 'challenge_check')]
    public function checkChallenge(Request $request, EntityManagerInterface $em) {
        if ($request->get('token') === $this->getParameter('token.api')):
            if ($challenge_id = $request->get('challenge_id')):
                $challenge = $em->getRepository(Challenge::class)->find($challenge_id);
                if ($request->get('isValid') === "true"):
                    $challenge->setValidity(true);
                else: $challenge->setValidity(false); endif;
                $em->persist($challenge);
                $em->flush();
            else:
                return $this->json(['status' => 'error', ['message' => "Challenge ID not found!"]]);
            endif;
        endif;
        return $this->json(['status' => 'error', 'message' => 'Fake token.']);
    }
}

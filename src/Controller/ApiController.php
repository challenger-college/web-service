<?php

namespace App\Controller;

use App\Entity\Challenge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/challenges', name: 'api_challenges')]
    public function index(EntityManagerInterface $em): Response
    {
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
    }
}

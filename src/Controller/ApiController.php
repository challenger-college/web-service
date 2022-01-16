<?php

namespace App\Controller;

use App\Entity\Challenge;
use App\Entity\Exercice;
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
            foreach ($em->getRepository(Challenge::class)->findBy([], ['createDate' => 'DESC']) ?? [] as $challenge):
                if (!$challenge->getValidity()):
                    $challenges[] = $challenge->array();
                endif;
            endforeach;

            return $this->json([
                'status' => 'success',
                'challenges' => $challenges ?? []
            ]);
        endif;

        return $this->json(['status' => 'error', 'message' => 'Fake token.']);
    }

    #[Route('/api/challenge/{challenge_id}/check', name: 'challenge_check')]
    public function checkChallenge(Request $request, string $challenge_id, EntityManagerInterface $em) {
        if ($request->get('token') === $this->getParameter('token.api')):
            $challenge = $em->getRepository(Challenge::class)->find($challenge_id);
            if ($request->get('isValid') === "true"):
                $challenge->setValidity(true);
            else: $challenge->setValidity(false); endif;
            $em->persist($challenge);
            $em->flush();
            return $this->json(['status' => 'success', 'message' => 'This challenge was updated.']);
        endif;
        return $this->json(['status' => 'error', 'message' => 'Fake token.']);
    }

    #[Route('/api/exercices', name: 'exercices')]
    public function exercices(Request $request, EntityManagerInterface $em) {
        if ($request->get('token') === $this->getParameter('token.api')):
            foreach ($em->getRepository(Exercice::class)->findBy(['validated' => false], ['createDate' => 'DESC']) ?? [] as $exercice):
                $exercices[] = [
                    'id' => $exercice->getId(),
                    'content' => $exercice->getContent(),
                    'challenge' => $exercice->getChallenge()->array()
                ];
            endforeach;
        endif;
    }
}

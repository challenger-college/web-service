<?php

namespace App\Controller;

use App\Entity\Exercice;
use App\Entity\Challenge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    #[Route('/api/challenges', name: 'api_challenges')]
    public function challenges(Request $request, EntityManagerInterface $em): Response
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

    #[Route('/api/challenge/{challenge_id}/check', name: 'api_challenge_check')]
    public function challengeCheck(Request $request, string $challenge_id, EntityManagerInterface $em): Response {
        if ($request->get('token') === $this->getParameter('token.api')):
            $challenge = $em->getRepository(Challenge::class)->find($challenge_id);
            if (!$challenge): return $this->json(['status' => 'error', 'message' => 'Challenge not found.']); endif;
            if ($request->get('isValid') === "true"):
                $challenge->setValidity(true);
            else: $challenge->setValidity(false); endif;
            $challenge->setTemplate($request->get('template'));

            $em->persist($challenge);
            $em->flush();
            return $this->json(['status' => 'success', 'message' => 'This challenge was updated.']);
        endif;
        return $this->json(['status' => 'error', 'message' => 'Fake token.']);
    }

    #[Route('/api/exercices', name: 'api_exercices')]
    public function exercices(Request $request, EntityManagerInterface $em): Response {
        if ($request->get('token') === $this->getParameter('token.api')):
            foreach ($em->getRepository(Exercice::class)->findBy(['validated' => false], ['createDate' => 'DESC']) ?? [] as $exercice):
                $exercices[] = [
                    'id' => $exercice->getId(),
                    'content' => $exercice->getContent(),
                    'challenge' => $exercice->getChallenge()->array(),
                ];
            endforeach;
            return $this->json(['status' => 'success', 'exercices' => $exercices]);
        endif;

        return $this->json(['status' => 'error', 'message' => 'Fake token.']);
    }

    #[Route('/api/exercice/{exercice_id}/check', name: 'api_exercice_check')]
    public function exerciceCheck(Request $request, string $exercice_id, EntityManagerInterface $em): Response {
        if ($request->get('token') === $this->getParameter('token.api')):
            $exercice = $em->getRepository(Exercice::class)->find($exercice_id);
            if (!$exercice): return $this->json(['status' => 'error', 'message' => 'Exercice not found.']); endif;
            if ($request->get('isValid') === "true"):
                $exercice->setValidated(true);
            else: $exercice->setValidated(false); endif;
            $em->persist($exercice);
            $em->flush();
            return $this->json(['status' => 'success', 'message' => 'This exercice was updated.']);
        endif;

        return $this->json(['status' => 'error', 'message' => 'Fake token.']);
    }
}

<?php

namespace App\Controller\Api;

use App\Entity\Challenge;
use App\Service\ChallengeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChallengeApi extends AbstractController
{
    #[Route('/api/challenge/{challenge_id}', name: 'api_challenge', methods: ['POST', 'PUT'], format: 'json', defaults: ['challenge_id' => null])]
    public function challenge(Request $request, ChallengeService $challengeService, ?string $challenge_id = null): JsonResponse
    {
        if ($request->isMethod('PUT')) {
            $challenge = $challengeService->update($challenge_id);
        } elseif ($request->isMethod('POST')) {
            $challenge = $challengeService->create();
        }

        return $this->json($challenge, Response::HTTP_OK);
    }

    #[Route('/api/challenges', name: 'api_challenges')]
    public function challenges(Request $request, EntityManagerInterface $em): JsonResponse
    {
        if ($request->get('token') === $this->getParameter('token.api')) {
            foreach ($em->getRepository(Challenge::class)->findBy([], ['createDate' => 'ASC']) ?? [] as $challenge) {
                if (null === $challenge->getValidity()) {
                    $challenges[] = $challenge->array();
                }
            }

            return $this->json($challenges ?? [], Response::HTTP_OK);
        }

        return $this->json(['error' => 'Fake token.'], Response::HTTP_UNAUTHORIZED);
    }

    #[Route('/api/challenge/{challenge_id}/check', name: 'api_challenge_check')]
    public function challengeCheck(Request $request, string $challenge_id, EntityManagerInterface $em): JsonResponse
    {
        if ($request->get('token') === $this->getParameter('token.api')) {
            $challenge = $em->getRepository(Challenge::class)->find($challenge_id);
            if (!$challenge) {
                return $this->json(['error' => 'Challenge not found.'], Response::HTTP_NOT_FOUND);
            }
            if (empty($request->get('isValid'))) {
                return $this->json(['error' => 'Parameter <isValid> not found.'], Response::HTTP_BAD_REQUEST);
            }
            if (empty($request->get('template'))) {
                return $this->json(['error' => 'Parameter <template> not found.'], Response::HTTP_BAD_REQUEST);
            }

            if ('true' === $request->get('isValid') || true === $request->get('isValid')) {
                $challenge->setValidity(true);
            } else {
                $challenge->setValidity(false);
            }

            $challenge->setTemplate($request->get('template'));

            $em->persist($challenge);
            $em->flush();

            return $this->json(['message' => 'This challenge was updated.'], Response::HTTP_CREATED);
        }

        return $this->json(['error' => 'Fake token.'], Response::HTTP_UNAUTHORIZED);
    }
}

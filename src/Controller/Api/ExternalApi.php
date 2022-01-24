<?php

namespace App\Controller\Api;

use App\Entity\Challenge;
use App\Entity\Error;
use App\Entity\Exercise;
use App\Entity\Result;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExternalApi extends AbstractController
{
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

    #[Route('/api/exercises', name: 'api_exercises')]
    public function exercises(Request $request, EntityManagerInterface $em): JsonResponse
    {
        if ($request->get('token') === $this->getParameter('token.api')) {
            foreach ($em->getRepository(Exercise::class)->findBy([], ['createDate' => 'ASC']) ?? [] as $exercise) {
                if (null === $exercise->getValidated() && $exercise->getOnload()) {
                    $exercises[] = $exercise->array();
                }
            }

            return $this->json($exercises ?? [], Response::HTTP_OK);
        }

        return $this->json(['error' => 'Fake token.'], Response::HTTP_UNAUTHORIZED);
    }

    #[Route('/api/exercise/{exercise_id}/check/{result_id}', name: 'api_exercise_check')]
    public function exerciseCheck(Request $request, string $exercise_id, string $result_id, EntityManagerInterface $em): JsonResponse
    {
        if ($request->get('token') === $this->getParameter('token.api')) {
            $exercise = $em->getRepository(Exercise::class)->find($exercise_id);
            if (!$exercise) {
                return $this->json(['error' => 'Exercise not found.'], Response::HTTP_NOT_FOUND);
            }

            if (empty($request->get('isValid'))) {
                return $this->json(['error' => 'Parameter <isValid> not found.'], Response::HTTP_BAD_REQUEST);
            }

            $result = $em->getRepository(Result::class)->find($result_id);
            if (!$result) {
                return $this->json(['error' => 'Result not found.'], Response::HTTP_NOT_FOUND);
            }

            $result
                ->setExercise($exercise)
                ->setTime($request->get('time'))
                ->setOutput($request->get('output'));
            if ('true' === $request->get('isValid') || true === $request->get('isValid')) {
                $exercise->setValidated(true);
            } else {
                $exercise->setValidated(false);
                if ($request->get('error')) {
                    $error = new Error();
                    $error->setMessage($request->get('error'))->setResult($result);
                }
                foreach ($request->get('errors') ?? [] as $message) {
                    $error = new Error();
                    $error->setMessage($message)->setResult($result);
                }
            }

            $exercise->setOnload(false);

            $em->persist($result);
            $em->flush();

            return $this->json(['message' => 'This exercise was updated.'], Response::HTTP_CREATED);
        }

        return $this->json(['error' => 'Fake token.'], Response::HTTP_UNAUTHORIZED);
    }
}

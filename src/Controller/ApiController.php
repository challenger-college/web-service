<?php

namespace App\Controller;

use App\Entity\Error;
use App\Entity\Result;
use App\Entity\Exercise;
use App\Entity\Challenge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    #[Route('/api/challenges', name: 'api_challenges')]
    public function challenges(Request $request, EntityManagerInterface $em): JsonResponse
    {
        if ($request->get('token') === $this->getParameter('token.api')):
            foreach ($em->getRepository(Challenge::class)->findBy([], ['createDate' => 'ASC']) ?? [] as $challenge):
                if ($challenge->getValidity() === null):
                    $challenges[] = $challenge->array();
                endif;
            endforeach;

            return $this->json($challenges ?? [], 200);
        endif;

        return $this->json(['error' => 'Fake token.'], 401);
    }

    #[Route('/api/challenge/{challenge_id}/check', name: 'api_challenge_check')]
    public function challengeCheck(Request $request, string $challenge_id, EntityManagerInterface $em): JsonResponse {
        if ($request->get('token') === $this->getParameter('token.api')):
            $challenge = $em->getRepository(Challenge::class)->find($challenge_id);
            if (!$challenge): return $this->json(['error' => 'Challenge not found.'], 404); endif;
            if (empty($request->get('isValid'))):
                return $this->json(['error' => 'Parameter <isValid> not found.'], 400);
            endif;
            if (empty($request->get('template'))):
                return $this->json(['error' => 'Parameter <template> not found.'], 400);
            endif;

            if ($request->get('isValid') === "true" || $request->get('isValid') === true):
                $challenge->setValidity(true);
            else: $challenge->setValidity(false); endif;

            $challenge->setTemplate($request->get('template'));

            $em->persist($challenge);
            $em->flush();
            return $this->json(['message' => 'This challenge was updated.'], 201);
        endif;
        return $this->json(['error' => 'Fake token.'], 401);
    }

    #[Route('/api/exercises', name: 'api_exercises')]
    public function exercises(Request $request, EntityManagerInterface $em): JsonResponse {
        if ($request->get('token') === $this->getParameter('token.api')):
            foreach ($em->getRepository(Exercise::class)->findBy([], ['createDate' => 'ASC']) ?? [] as $exercise):
                if ($exercise->getValidated() === null):
                    $exercises[] = [
                        'id' => $exercise->getId(),
                        'content' => $exercise->getContent(),
                        'challenge' => $exercise->getChallenge()->array(),
                    ];
                endif;
            endforeach;
            return $this->json($exercises ?? [], 200);
        endif;

        return $this->json(['error' => 'Fake token.'], 401);
    }

    #[Route('/api/exercise/{exercise_id}/check', name: 'api_exercise_check')]
    public function exerciseCheck(Request $request, string $exercise_id, EntityManagerInterface $em): JsonResponse {
        if ($request->get('token') === $this->getParameter('token.api')):
            $exercise = $em->getRepository(Exercise::class)->find($exercise_id);
            if (!$exercise): return $this->json(['error' => 'Exercise not found.'], 404); endif;

            if (empty($request->get('isValid'))):
                return $this->json(['error' => 'Parameter <isValid> not found.'], 400);
            endif;
            if (empty($request->get('template'))):
                return $this->json(['error' => 'Parameter <template> not found.'], 400);
            endif;

            if ($request->get('isValid') === "true" || $request->get('isValid') === true):
                $exercise->setValidated(true);
            else: $exercise->setValidated(false); endif;
            $em->persist($exercise);
            $em->flush();
            return $this->json(['message' => 'This exercise was updated.'], 201);
        endif;

        return $this->json(['error' => 'Fake token.'], 401);
    }

    #[Route('/api/exercise/{exercise_id}/result', name: 'api_exercice_result')]
    public function exerciseResult(string $exercise_id, Request $request, EntityManagerInterface $em): JsonResponse {
        if ($request->get('token') === $this->getParameter('token.api')):
            $exercise = $em->getRepository(Exercise::class)->findOneBy(['id' => $exercise_id, 'validated' => true]);
            if (!$exercise): return $this->json(['error' => 'Exercise not found.'], 404); endif;
            $result = new Result();
            $result->setTime($request->get('time'));
            $result->setExercice($exercise);
            foreach ($request->get('errors') ?? [] as $message):
                $error = new Error();
                $error->setMessage($message);
                $result->addError($error);
                $em->persist($error);
            endforeach;
            $em->persist($result);
            $em->flush();

            return $this->json([$result->array()], 200);
        endif;

        return $this->json(['error' => 'Fake token.'], 401);
    }
}

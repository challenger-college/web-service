<?php

namespace App\Controller;

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

class ApiController extends AbstractController
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
                if (null === $exercise->getValidated()) {
                    $exercises[] = [
                        'id' => $exercise->getId(),
                        'content' => $exercise->getContent(),
                        'challenge' => $exercise->getChallenge()->array(),
                    ];
                }
            }

            return $this->json($exercises ?? [], 200);
        }

        return $this->json(['error' => 'Fake token.'], Response::HTTP_UNAUTHORIZED);
    }

    #[Route('/api/exercise/{exercise_id}/submit', name: 'api_exercise_submit')]
    public function exerciseSubmit(Request $request, string $exercise_id, EntityManagerInterface $em): JsonResponse
    {
        $exercise = $em->getRepository(Exercise::class)->findOneBy(['id' => $exercise_id]);
        if ($request->isMethod('POST') && $exercise->getAuthor() === $this->getUser()) {
            $exercise->setContent($request->get('content'));
            $exercise->setValidated(null);
            $em->persist($exercise);
            $em->flush();

            return $this->json(['message' => 'Exercise submited for validation.'], Response::HTTP_CREATED);
        }

        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @todo Add anti-spam security
     */
    #[Route('/api/exercise/{exercise_id}/check', name: 'api_exercise_check')]
    public function exerciseCheck(Request $request, string $exercise_id, EntityManagerInterface $em): JsonResponse
    {
        if ($request->get('token') === $this->getParameter('token.api')) {
            $exercise = $em->getRepository(Exercise::class)->find($exercise_id);
            if (!$exercise) {
                return $this->json(['error' => 'Exercise not found.'], Response::HTTP_NOT_FOUND);
            }

            if (empty($request->get('isValid'))) {
                return $this->json(['error' => 'Parameter <isValid> not found.'], Response::HTTP_BAD_REQUEST);
            }

            $result = new Result();

            if ('true' === $request->get('isValid') || true === $request->get('isValid')) {
                $exercise->setValidated(true);
            } else {
                $exercise->setValidated(false);
                $error = new Error();
                $error->setMessage($request->get('error') ?? "")->setResult($result);
            }

            $result
                ->setExercise($exercise)
                ->setTime($request->get('time'))
                ->setOutput($request->get('output'));
                
            $em->persist($result);
            $em->flush();

            $em->persist($exercise);
            $em->flush();

            return $this->json(['message' => 'This exercise was updated.'], Response::HTTP_CREATED);
        }

        return $this->json(['error' => 'Fake token.'], Response::HTTP_UNAUTHORIZED);
    }

    #[Route('/api/exercise/{exercise_id}/result/{result_id}', name: 'api_exercise_result', defaults: ['result_id' => null])]
    public function exerciseResult(string $exercise_id, ?string $result_id = null, Request $request, EntityManagerInterface $em): JsonResponse
    {
        if ($request->isMethod('POST') && $request->get('token') === $this->getParameter('token.api')) {
            $exercise = $em->getRepository(Exercise::class)->findOneBy(['id' => $exercise_id, 'validated' => true]);
            if (!$exercise) {
                return $this->json(['error' => 'Exercise not found.'], Response::HTTP_NOT_FOUND);
            }
            $result = new Result();
            $result->setOutput($request->get('output'));
            $result->setTime($request->get('time'));
            $result->setExercise($exercise);
            foreach ($request->get('errors') ?? [] as $message) {
                $error = new Error();
                $error->setMessage($message);
                $result->addError($error);
                $em->persist($error);
            }
            $em->persist($result);
            $em->flush();

            return $this->json([$result->array()], Response::HTTP_OK);
        } elseif ($request->isMethod('GET') && $result_id) {
            $exercise = $em->getRepository(Exercise::class)->findOneBy(['id' => $exercise_id, 'author' => $this->getUser()]);
            if (!$exercise) {
                return $this->json(['error' => 'Exercise not found.'], Response::HTTP_NOT_FOUND);
            }

            $result = $em->getRepository(Result::class)->findOneBy(['id' => $result_id, 'exercise' => $exercise]);
            dd($result);
            if (!$result) {
                $result = new Result();
                $result->setOutput('Your exercise submission is in queue list.');
                return $this->json($result->array(), Response::HTTP_PARTIAL_CONTENT);
            }

            return $this->json([$result->array()], Response::HTTP_ACCEPTED);
        } elseif (!$this->getUser()) {
            return $this->json(['error' => 'You need to be connected.'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json(['error' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
    }
}

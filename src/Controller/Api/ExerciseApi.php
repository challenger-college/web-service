<?php

namespace App\Controller\Api;

use App\Entity\Error;
use App\Entity\Exercise;
use App\Entity\Result;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExerciseApi extends AbstractController
{
    /**
     * @todo Add anti-spam security
     */
    #[Route('/api/exercise/{exercise_id}/submit', name: 'api_exercise_submit')]
    public function exerciseSubmit(Request $request, string $exercise_id, EntityManagerInterface $em): JsonResponse
    {
        if (!$request->isMethod('POST')) {
            return $this->json(['error' => 'This is POST endpoint.'], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        /** @var Exercise $exercise */
        $exercise = $em->getRepository(Exercise::class)->findOneBy(['id' => $exercise_id]);

        if (!$exercise) {
            return $this->json(['error' => 'Exercise not found.'], Response::HTTP_NOT_FOUND);
        } elseif ($exercise->getAuthor() !== $this->getUser()) {
            return $this->json(['error' => 'You must be logged in as the correct user to access this exercise.'], Response::HTTP_UNAUTHORIZED);
        }

        $exercise->setContent($request->get('content') ?? '');
        $exercise->setValidated(null);
        $exercise->setUpdateDate(new DateTime());
        $exercise->addResult((new Result())->setExercise($exercise));
        $exercise->setOnLoad(true);

        $em->persist($exercise);
        $em->flush();

        return $this->json($exercise->array(), Response::HTTP_CREATED);
    }

    /**
     * @todo Add anti-spam security
     */
    #[Route('/api/exercise/{exercise_id}/result/{result_id}', name: 'api_exercise_result', defaults: ['result_id' => null])]
    public function exerciseResult(string $exercise_id, string $result_id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $exercise = $em->getRepository(Exercise::class)->findOneBy(['id' => $exercise_id, 'author' => $this->getUser()]);
        if (!$exercise) {
            return $this->json(['error' => 'Exercise not found.'], Response::HTTP_NOT_FOUND);
        }

        $result = $em->getRepository(Result::class)->findOneBy(['id' => $result_id, 'exercise' => $exercise]);
        if (!$result) {
            return $this->json(['error' => 'Result not found.'], Response::HTTP_NOT_FOUND);
        }

        if ($result->getOutput()) {
            return $this->json($result->array(), Response::HTTP_OK);
        } else {
            return $this->json($result->array(), Response::HTTP_ACCEPTED);
        }
    }

    #[Route('/api/exercises', name: 'api_exercises', methods: 'GET')]
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

    #[Route('/api/exercise/{exercise_id}/check/{result_id}', name: 'api_exercise_check', methods: 'POST')]
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
                    $error->setMessage($request->get('error'))->setLineNumber($request->get('line_number'))->setResult($result);
                }
                foreach ($request->get('errors') ?? [] as $message) {
                    $error = new Error();
                    $error->setMessage($message)->setLineNumber($request->get('line_number'))->setResult($result);
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

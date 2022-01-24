<?php

namespace App\Controller\Api;

use App\Entity\Exercise;
use App\Entity\Result;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InternalApi extends AbstractController
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
        if (!$exercise->getResults()->last()->getOutput() && !$exercise->getResults()->last()->getErrors()) {
            $exercise->addResult((new Result())->setExercise($exercise));
        }

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

        return $this->json([$result->array()], Response::HTTP_ACCEPTED);
    }
}

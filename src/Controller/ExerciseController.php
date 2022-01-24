<?php

namespace App\Controller;

use App\Entity\Challenge;
use App\Entity\Exercise;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\Error;

class ExerciseController extends AbstractController
{
    #[Route('/exercises', name: 'exercises')]
    public function exercises(EntityManagerInterface $em): Response
    {
        $exercises = $em->getRepository(Exercise::class)->findBy(['user' => $this->getUser()]);

        return $this->render('app/exercises.html.twig', ['exercises' => $exercises]);
    }

    /**
     * @todo Check if User is connected
     */
    #[Route('/exercise/{challenge_id}/{exercise_id}', name: 'exercise', defaults: ['exercise_id' => null])]
    public function exercise(Request $request, Session $session, string $challenge_id, ?string $exercise_id = null, EntityManagerInterface $em): Response
    {
        $challenge = $em->getRepository(Challenge::class)->find($challenge_id);

        if (!$challenge) {
            return new Response(new Error('Challenge not found.'), Response::HTTP_NOT_FOUND);
        } elseif (!$challenge->getValidity()) {
            return new Response(new Error('Challenge need to been re-validated.'), Response::HTTP_FAILED_DEPENDENCY);
        }

        $exercise = $em->getRepository(Exercise::class)
            ->findOneBy([
                'author' => $this->getUser(),
                'challenge' => $challenge,
                'id' => $exercise_id,
            ]
        );

        if (!$exercise) {
            $exercise = new Exercise();
            $exercise
                ->setChallenge($challenge)
                ->setAuthor($this->getUser())
                ->setValidated(null)
                ->setContent($challenge->getTemplate() ?? '');

            $em->persist($exercise);
            $em->flush();
        }

        $session->set('challenge_id', $challenge->getId());
        $session->set('exercise_id', $exercise->getId());

        return $this->render('exercise/exercise.html.twig', ['challenge' => $challenge, 'exercise' => $exercise]);
    }

    #[Route('/exercise/{exercise_id}/delete', name: 'exercise_delete')]
    public function delete(string $exercise_id, EntityManagerInterface $em): Response
    {
        $exercise = $em->getRepository(Exercise::class)->find($exercise_id);
        $em->remove($exercise);
        $em->flush();

        return $this->redirectToRoute('exercises');
    }
}

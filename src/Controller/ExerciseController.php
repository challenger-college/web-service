<?php

namespace App\Controller;

use App\Entity\Challenge;
use App\Entity\Exercise;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExerciseController extends AbstractController
{
    #[Route('/exercises', name: 'exercises')]
    public function exercises(EntityManagerInterface $em): Response
    {
        $exercises = $em->getRepository(Exercise::class)->findBy(['user' => $this->getUser()]);
        return $this->render('app/exercises.html.twig', ['exercises' => $exercises]);
    }

    #[Route('/exercise/{challenge_id}/{exercise_id}', name: 'exercise', defaults: ["exercise_id" => null])]
    public function exercise(Request $request, EntityManagerInterface $em, string $challenge_id, ?string $exercise_id = null): Response
    {
        $challenge = $em->getRepository(Challenge::class)->findOneBy(['id' => $challenge_id, 'validity' => true]);
        
        if ($exercise_id):
            $exercise = $em->getRepository(Exercise::class)->findOneBy(['id' => $exercise_id, 'challenge' => $challenge_id]);
        else: 
            $exercise = new Exercise(); 
            $exercise->setChallenge($challenge);
            $exercise->setAuthor($this->getUser());
            $exercise->setCreateDate(new DateTime());
            $exercise->setValidated(null);
            $exercise->setContent($challenge->getTemplate() ?? "");
            $em->persist($exercise);
            $em->flush();
        endif;

        if ($request->isMethod('POST') && $exercise->getAuthor() === $this->getUser()):
            $exercise->setContent($request->get('content'));
            $exercise->setValidated(null);
            $em->persist($exercise);
            $em->flush();
            return $this->json(['message' => 'Exercise submited for validation.'], 201);
        endif;


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

<?php

namespace App\Controller;

use App\Entity\Challenge;
use App\Repository\ChallengeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChallengeController extends AbstractController
{
    #[Route('/challenges', name: 'challenges')]
    public function challenges(EntityManagerInterface $em): Response
    {
        $challenges = $em->getRepository(Challenge::class)->findBy([], ['createDate' => 'DESC']);

        return $this->render('challenge/challenges.html.twig', ['challenges' => $challenges]);
    }

    #[Route('/challenge/{challenge_id}', name: 'challenge', defaults: ['challenge_id' => null])]
    public function challenge(ChallengeRepository $challengeRepository, ?string $challenge_id = null): Response
    {
        return $this->render('challenge/challenge.html.twig', [
            'challenge' => $challengeRepository->findOneBy(['id' => $challenge_id]),
        ]);
    }

    #[Route('/challenge/{challenge_id}/delete', name: 'challenge_delete')]
    public function delete(string $challenge_id, EntityManagerInterface $em): Response
    {
        $challenge = $em->getRepository(Challenge::class)->find($challenge_id);
        $em->remove($challenge);
        $em->flush();

        return $this->redirectToRoute('challenges');
    }
}

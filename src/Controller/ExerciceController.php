<?php

namespace App\Controller;

use App\Entity\Challenge;
use App\Entity\Exercice;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExerciceController extends AbstractController
{
    #[Route('/exercice/{challenge_id}/{exercice_id}', name: 'exercice')]
    public function exercice(Request $request, string $challenge_id, ?string $exercice_id = null, EntityManagerInterface $em): Response
    {
        $challenge = $em->getRepository(Challenge::class)->findOneBy(['id' => $challenge_id, 'validity' => true]);
     
        if ($exercice_id):
            $exercice = $em->getRepository(Exercice::class)->findOneBy(['id' => $exercice_id, 'challenge' => $challenge_id]);
        else: 
            $exercice = new Exercice(); 
            $exercice->setChallenge($challenge);
            $exercice->setAuthor($this->getUser());
            $exercice->setCreateDate(new DateTime());
            $exercice->setValidated(false);
            $exercice->setContent($challenge->getTemplate());
            $em->persist($exercice);
            $em->flush();
        endif;

        if ($request->isMethod('POST') && $exercice->getAuthor() === $this->getUser()):
            $exercice->setContent($request->get('content'));
            $exercice->setValidated(false);
            $em->persist($exercice);
            $em->flush();

            return $this->json(['status' => 'success', 'message' => 'Exercice submited for validation.']);
        endif;


        return $this->render('exercice/exercice.html.twig', ['challenge' => $challenge, 'exercice' => $exercice]);
    }
}

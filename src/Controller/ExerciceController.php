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
    #[Route('/exercice/{challenge_id}/{exercice_id}', name: 'exercice', defaults: ["exercice_id" => null])]
    public function exercice(Request $request, EntityManagerInterface $em, string $challenge_id, ?string $exercice_id = null): Response
    {
        $challenge = $em->getRepository(Challenge::class)->findOneBy(['id' => $challenge_id, 'validity' => true]);
        
        if ($exercice_id):
            $exercice = $em->getRepository(Exercice::class)->findOneBy(['id' => $exercice_id, 'challenge' => $challenge_id]);
        else: 
            $exercice = new Exercice(); 
            $exercice->setChallenge($challenge);
            $exercice->setAuthor($this->getUser());
            $exercice->setCreateDate(new DateTime());
            $exercice->setValidated(null);
            $exercice->setContent($challenge->getTemplate() ?? "");
            $em->persist($exercice);
            $em->flush();
        endif;

        if ($request->isMethod('POST') && $exercice->getAuthor() === $this->getUser()):
            $exercice->setContent($request->get('content'));
            $exercice->setValidated(null);
            $em->persist($exercice);
            $em->flush();
            return $this->json(['message' => 'Exercice submited for validation.'], 201);
        endif;


        return $this->render('exercice/exercice.html.twig', ['challenge' => $challenge, 'exercice' => $exercice]);
    }
}

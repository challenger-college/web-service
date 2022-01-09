<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    #[Route('/', name: 'app')]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST')):
            dd($request->request);
        endif;
        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }
}

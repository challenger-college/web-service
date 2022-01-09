<?php

namespace App\Controller;

use App\Entity\Test;
use App\Entity\Output;
use App\Entity\Challenge;
use App\Entity\Input;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    #[Route('/', name: 'app')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST') && $this->getUser()):
            $challenge = new Challenge();
            $challenge->setTitle($request->get('title'));
            $challenge->setDescription($request->get('description'));
            $challenge->setConstraints($request->get('constraints'));
            $challenge->setTimeout($request->get('timeout'));
            $challenge->setFunctionName($request->get('function_name'));
            
            foreach ($request->get('inputs_value') ?? [] as $index => $values):
                foreach ($values as $key => $value):
                    $tests[$index]['inputs'][$key]['value'] = $value;
                endforeach;
            endforeach;
            foreach ($request->get('inputs_name') ?? [] as $index => $names):
                foreach ($names as $key => $name):
                    $tests[$index]['inputs'][$key]['name'] = $name;
                endforeach;
            endforeach;
            foreach ($request->get('output') ?? [] as $index => $name):
                    $tests[$index]['output'] = $name;
            endforeach;

            foreach ($tests ?? [] as $test):
                $Test = new Test();

                $output = new Output();
                $output->setValue($test['output']);
                $output->setTest($Test);
                $em->persist($output);

                foreach ($test['inputs'] as $input):
                    $Input = new Input();
                    $Input->setName($input['name']);
                    $Input->setValue($input['value']);
                    $Input->setTest($Test);
                    $em->persist($Input);
                endforeach;

                $challenge->addTest($Test);
                $em->persist($Test);
            endforeach;

            $challenge->setAuthor($this->getUser());
            $challenge->setCreateDate(new DateTime());
            $challenge->setUpdateDate(new DateTime());
            $em->persist($challenge);
            $em->flush();
        endif;
        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }
}

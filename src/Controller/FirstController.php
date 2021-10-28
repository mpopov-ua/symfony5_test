<?php


namespace App\Controller;


use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FirstController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function homepage (): Response
    {
        return new Response('My first page to Symfony: Homepage');
    }

    /**
     * @Route("/second/{any}")
     *
     */
    public function show ($any): Response
    {
        $answers = [
            'First question: ',
            'Second question',
            'Third question'
        ];
        return $this->render('first/show.html.twig', [
            'first' => ucwords(str_replace('-', ' ', $any)),
            'answers' => $answers
        ]);
    }
}
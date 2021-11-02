<?php


namespace App\Controller;


use Amp\Http\Client\Request;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FirstController extends AbstractController
{
    /**
     * @return Response
     */
    #[Route('/home', name: 'homepage')]
    public function homepage (): Response
    {
        return new Response('My first page to Symfony: Homepage');
    }

    /**
     * @param int $any
     * @return Response
     */
    #[Route ('/company/{any}', name: 'about')]
    public function show (int $any): Response
    {
        $answers = [
            'First question:',
            'Second question',
            'Third question'
        ];
        dump($any, $this);

        return $this->render('first/show.html.twig', [
            'first' => ucwords(str_replace('-', ' ', $any)),
            'answers' => $answers
        ]);
    }

}
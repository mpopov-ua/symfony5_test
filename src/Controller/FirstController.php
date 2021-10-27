<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FirstController
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
        return new Response(sprintf(
            'My second page by Symfony: "%s"!',
            ucwords(str_replace('-', ' ', $any))
        ));
    }
}
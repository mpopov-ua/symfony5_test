<?php


namespace App\Controller;


use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecondController extends AbstractController
{
    /**
     * @param int $companyID
     * @return Response
     */
    #[Route ('/about/{companyID}')]
    public function second (int $companyID): Response
    {
        $parameters = [
            'FirstAbout',
            'SecondAbout',
            'ThirdAbout'
        ];
        return $this->render('first/second.html.twig', parameters: [
            'parameters'=>$parameters,
            'companyID'=>$companyID
        ]);
    }
}
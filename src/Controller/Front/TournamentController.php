<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TournamentController extends AbstractController
{
    #[Route('/tournament', name: 'front_tournament')]
    public function index(): Response
    {
        return $this->render('front/tournament/index.html.twig', [
            'controller_name' => 'TournamentController',
        ]);
    }
}

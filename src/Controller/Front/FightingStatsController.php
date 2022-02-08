<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FightingStatsController extends AbstractController
{
    #[Route('/stats', name: 'front_fighting_stats')]
    public function index(): Response
    {
        return $this->render('front/fighting_stats/index.html.twig', [
            'controller_name' => 'Front/FightingStatsController',
        ]);
    }
}

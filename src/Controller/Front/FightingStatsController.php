<?php

namespace App\Controller\Front;

use App\Repository\FightingStatsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/stats')]
class FightingStatsController extends AbstractController
{
    #[Route('/', name: 'fighting_stats_index', methods: ['GET'])]
    public function index(FightingStatsRepository $fsRepository): Response
    {
        return $this->render('front/fighting_stats/index.html.twig', [
            'controller_name' => 'Front/FightingStatsController',
            'fs' => $fsRepository->findBy([], ['rank' => 'ASC'])
        ]);
    }
}

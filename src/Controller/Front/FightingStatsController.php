<?php

namespace App\Controller\Front;

use App\Repository\FightingStatsRepository;
use App\Entity\FightingStats;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Faker\Factory;
use App\Entity\User;
use App\Utils\UArray;

#[Route('/stats')]
class FightingStatsController extends AbstractController
{
    #[Route('/', name: 'front_fighting_stats', methods: ['GET'])]
    public function index(FightingStatsRepository $fsRepository): Response
    {
        return $this->render('front/fighting_stats/index.html.twig', [
            'controller_name' => 'Front/FightingStatsController',
            'fs' => $fsRepository->findBy([], ['rank' => 'ASC'])
        ]);
    }
}

<?php

namespace App\Controller\Front;

use App\Entity\Tournament;
use App\Service\TournamentService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/tournament')]
class TournamentController extends AbstractController
{
    #[Route('/', name: 'front_tournament', methods: ['GET'])]
    public function index(TournamentService $tournamentService): Response
    {
        return $this->render('front/tournament/index.html.twig', [
            'controller_name' => 'TournamentController',
            'tournaments' => $tournamentService->findIncoming()
        ]);
    }

    #[Route('/{id}', name: 'front_tournament_show', methods: ['GET'])]
    public function show(Tournament $tournament): Response 
    {
        return $this->render('front/tournament/show.html.twig', [
            'controller_name' => 'TournamentController',
            'tournament' => $tournament
        ]);
    }
}

<?php

namespace App\Controller\Front;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Tournament;
use App\Entity\FightingStats;
use App\Service\FightingStatsService;
use App\Repository\TournamentRepository;
use App\Repository\FightingStatsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/tournament')]
class TournamentController extends AbstractController
{
    #[Route('/', name: 'tournament_index', methods: ['GET', 'POST'])]
    public function index(Request $request, TournamentRepository $tournamentRepository): Response
    {
        if ($request->isMethod('POST') && !$this->isCsrfTokenValid('tournamentFilter', $request->request->get('_token'))) {
            $this->addFlash('red', "SecurityError");
            return $this->render('front/tournament/index.html.twig', [
                'tournaments' => $tournamentRepository->findBy(["status" => "AWAITING"], ["dateStart" => "ASC"]),
                'status' => "AWAITING"
            ]);
        }
        $status = $request->request->get('status') ?? "AWAITING";
        return $this->render('front/tournament/index.html.twig', [
            'tournaments' => $tournamentRepository->findBy(["status" => $status], ["dateStart" => "ASC"]),
            'status' => $status
        ]);
    }

    #[Route('/{id}', name: 'tournament_show', methods: ['GET'])]
    public function show(Tournament $tournament): Response 
    {
        return $this->render('front/tournament/show.html.twig', [
            'tournament' => $tournament
        ]);
    }
}

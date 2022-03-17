<?php

namespace App\Controller\Front;

use Faker\Factory;
use App\Entity\Tournament;
use App\Entity\FightingStats;
use App\Entity\User;
use App\Repository\TournamentRepository;
use App\Repository\FightingStatsRepository;
use App\Service\FightingStatsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/tournament')]
class TournamentController extends AbstractController
{
    #[Route('/', name: 'front_tournament', methods: ['GET'])]
    public function index(TournamentRepository $tournamentRepository, FightingStatsRepository $fs, FightingStatsService $fsS): Response
    {
        $faker = Factory::create();
        $manager = $this->getDoctrine()->getManager();
        $fighters = $manager->getRepository(User::class)->findByRole("ROLE_FIGHTER");
        foreach ($fighters as $fighter) {
            $object = (new FightingStats())
                ->setVictories($faker->numberBetween(0, 100))
                ->setDefeats($faker->numberBetween(0, 100))
                ->setRankingPoints($faker->randomDigit())
                ->setTarget($fighter);
                $fsS->placeRank($object);
            $fighter->setFightingStats($object);
            $manager->persist($object);
            $manager->flush();
        }
        dd("FIN");
        return $this->render('front/tournament/index.html.twig', [
            'controller_name' => 'TournamentController',
            'tournaments' => $tournamentRepository->findIncoming()
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

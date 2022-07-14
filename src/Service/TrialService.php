<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Trial;
use App\Entity\Tournament;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class TrialService
{
    const BASE_POINTS = 10.00;
    const WINS = [ "TIME" => 0, "KO" => 0.1, "TKO" => 0.2];
    const WIN_KO = 0.10;
    const WIN_TIME = 0;
    private $entityManager;
    private $userRepository;
    private $betService;
    private $fightingStatsService;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, BetService $betService,FightingStatsService $fightingStatsService)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->betService = $betService;
        $this->fightingStatsService = $fightingStatsService;
    }

    public function endTrial(Trial $trial, User $winner, string $winType)
    {
        $winner = $winner;
        $loser = $trial->getFighters()[0] !== $winner ? $trial->getFighters()[0] : $trial->getFighters()[1];

        $nbFighter = count($this->userRepository->findByRole("ROLE_FIGHTER"));

        $ratioRankWinner = ($winner->getFightingStats()->getRank() / $nbFighter);
        $ratioRankLooser = ($loser->getFightingStats()->getRank() / $nbFighter);
        $diffRank = abs($ratioRankWinner - $ratioRankLooser);

        $diffRank = $ratioRankWinner < $ratioRankLooser ? -($diffRank) : $diffRank;

        $ratioTrialWinner = $winner->getFightingStats()->getVictories() / $winner->getFightingStats()->getDefeats() > 1.1 ? 0.2 : -0.2;
        $ratioTrialLooser = $loser->getFightingStats()->getVictories() / $loser->getFightingStats()->getDefeats() > 1.1 ? -0.2 : 0.2;

        $winPoints = self::BASE_POINTS + (self::BASE_POINTS * ($diffRank + $ratioTrialWinner + self::WINS[$winType]));
        $lossPoints = -(self::BASE_POINTS + (self::BASE_POINTS * ($diffRank + $ratioTrialLooser)));

        $this->fightingStatsService->modifyRank($loser->getFightingStats(), $lossPoints);
        $this->fightingStatsService->modifyRank($winner->getFightingStats(), $winPoints);

        $trial->setWinner($winner);
        $trial->setVictoryType($winType);
        $trial->setStatus("ENDED");
        $this->entityManager->flush();

        $this->betService->closeBets(trial: $trial, winType: $winType);
        return;
    }
}
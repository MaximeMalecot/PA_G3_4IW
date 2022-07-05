<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Trial;
use App\Entity\Tournament;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class TrialService
{
    private $entityManager;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    public function endTrial(Trial $trial, User $winner, string $winType)
    {
        $winner = $winner;
        $loser = $trial->getFighters()[0] !== $winner ? $trial->getFighters()[0] : $trial->getFighters()[1];

        $nbFighter = count($this->userRepository->findByRole("ROLE_FIGHTER"));

        $ratioRankWinner = ($nbFighter * $winner->getFightingStats()->getRank())/100;
        $ratioRankLooser = ($nbFighter * $loser->getFightingStats()->getRank())/100;

        $ratioTrialWinner = $winner->getFightingStats()->getVictories() - $winner->getFightingStats()->getDefeats();
        $ratioTrialLooser = $loser->getFightingStats()->getVictories() - $loser->getFightingStats()->getDefeats();

        dd($ratioRankWinner, $ratioRankLooser);

    }
}
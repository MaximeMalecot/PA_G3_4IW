<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\FightingStats;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FightingStatsRepository;

class FightingStatsService
{
    private $entityManager;
    private $fightingStatsRepository;

    public function __construct(EntityManagerInterface $entityManager, FightingStatsRepository $fightingStatsRepository)
    {
        $this->entityManager = $entityManager;
        $this->fightingStatsRepository = $fightingStatsRepository;
    }

    public function modifyRank(FightingStats $fs, int $points){//IF LOSE POINTS = -100 ELSE POINTS = 100
        $incomingPoints = $fs->getRankingPoints() + $points;
        $conn = $this->entityManager->getConnection();
        $select = "SELECT * FROM fighting_stats WHERE ranking_points >= ? ORDER BY rank DESC";
        $res = $conn->executeQuery($select, [$incomingPoints])->fetchAllAssociative();
         if( !empty($res)){
            if($res[0]["ranking_points"] == $fs->getRankingPoints() + $points){
                $incomingRank = $res[0]["rank"];
            } else {
                $incomingRank = $res[0]["rank"] + 1;
            }
        } else {
            $incomingRank = 1;
        }

        if($fs->getRankingPoints() < $incomingPoints){ //UPRANK
            $update = "UPDATE fighting_stats SET rank = rank + 1 WHERE ranking_points < ? AND ranking_points >= ? AND id <> ?";
            $conn->executeStatement($update, [$incomingPoints, $fs->getRankingPoints(), $fs->getId()]);
        } else if($fs->getRankingPoints() > $incomingPoints) { //DOWNRANK
            $update = "UPDATE fighting_stats SET rank = rank - 1 WHERE ranking_points >= ?  AND ranking_points < ? AND id <> ?";
            $conn->executeStatement($update, [$incomingPoints, $fs->getRankingPoints(), $fs->getId()]);
            $incomingRank = $incomingRank - 1;
        }
        $fs->setRankingPoints($fs->getRankingPoints() + $points);
        $fs->setRank($incomingRank);
        $this->_em->persist($fs);
        $this->_em->flush();
        return;
    }

    public function placeRank(FightingStats $fs)
    {   
        $conn = $this->entityManager->getConnection();
        $select = "SELECT * FROM fighting_stats WHERE ranking_points >= ? ORDER BY rank DESC";
        $res = $conn->executeQuery($select, [$fs->getRankingPoints()])->fetchAllAssociative();
        if( !empty($res)){
            if($res[0]["ranking_points"] == $fs->getRankingPoints()){
                $fs->setRank($res[0]["rank"]);
            } else {
                $fs->setRank($res[0]["rank"] + 1);
            }
        } else {
            $fs->setRank(1);
        }
        $update = "UPDATE fighting_stats SET rank = rank + 1 WHERE ranking_points < ?";
        $conn->executeStatement($update, [$fs->getRankingPoints()]);
    }

    public function setNewFighter(User $user){
        $user->setRoles(['ROLE_FIGHTER']);
        $fs = new FightingStats();
        $fs->setRank($this->fightingStatsRepository->findMinRank());
        $fs->setTarget($user);
        $this->entityManager->persist($fs);
    }

}
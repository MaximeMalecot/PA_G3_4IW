<?php

namespace App\Repository;

use App\Entity\FightingStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FightingStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method FightingStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method FightingStats[]    findAll()
 * @method FightingStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FightingStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FightingStats::class);
    }

    public function modifyRank(FightingStats $fs, int $points){//IF LOSE POINTS = -100 ELSE POINTS = 100
        $incomingPoints = $fs->getRankingPoints() + $points;
        $conn = $this->getEntityManager()->getConnection();
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
            $res = $conn->executeStatement($update, [$incomingPoints, $fs->getRankingPoints(), $fs->getId()]);
        } else if($fs->getRankingPoints() > $incomingPoints) { //DOWNRANK
            $update = "UPDATE fighting_stats SET rank = rank - 1 WHERE ranking_points >= ?  AND ranking_points < ? AND id <> ?";
            $res = $conn->executeStatement($update, [$incomingPoints, $fs->getRankingPoints(), $fs->getId()]);
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
        $conn = $this->getEntityManager()->getConnection();
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
        $res = $conn->executeStatement($update, [$fs->getRankingPoints()]);
        return;
    }

    // /**
    //  * @return FightingStats[] Returns an array of FightingStats objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FightingStats
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

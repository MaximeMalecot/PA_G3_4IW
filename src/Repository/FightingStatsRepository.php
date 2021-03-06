<?php

namespace App\Repository;

use App\Entity\FightingStats;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

    public function findMinRank()
    {
        return $this->_em->createQueryBuilder()
            ->select('MAX(fs.rank)')
            ->from('App:FightingStats', 'fs')
            ->getQuery()
            ->getSingleScalarResult();
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

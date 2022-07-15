<?php

namespace App\Repository;

use App\Entity\Bet;
use App\Entity\Tournament;
use App\Entity\User;
use App\Entity\Trial;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Bet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bet[]    findAll()
 * @method Bet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bet::class);
    }

    public function findTrialWinners(Trial $trial)
    {
        $qb = $this->createQueryBuilder('b');
        return $qb->innerJoin('b.trial', 'tr')
            ->where('tr.id = :tid')
            ->andWhere('b.bettee = :winnerId')
            ->andWhere('b.victoryType = :victoryType')
            ->setParameters(['tid' => $trial->getId(), 'winnerId' => $trial->getWinner()->getId(), 'victoryType' => $trial->getVictoryType()])
            ->getQuery()
            ->getResult();
    }

    public function findTournamentWinners(Tournament $tournament)
    {
        $qb = $this->createQueryBuilder('b');
        return $qb->innerJoin('b.tournament', 'tn')
            ->where('tn.id = :tid')
            ->andWhere('b.bettee = :winnerId')
            ->setParameters(['tid' => $tournament->getId(), 'winnerId' => $tournament->getWinner()->getId()])
            ->getQuery()
            ->getResult();
    }


    // /**
    //  * @return Bet[] Returns an array of Bet objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Bet
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

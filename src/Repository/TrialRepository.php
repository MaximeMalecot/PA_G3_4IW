<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Trial;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Trial|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trial|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trial[]    findAll()
 * @method Trial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trial::class);
    }

    public function findIncomingTrials(User $user)
    {
        $qb = $this->createQueryBuilder('t');
        return $qb
            ->innerJoin('t.fighters', 'f')
            ->where('t.status = :status')
            ->andWhere($qb->expr()->isNotNull('t.adjudicate'))
            ->andWhere('f.id = :uid')
            ->setParameter('status', 'CREATED')
            ->setParameter('uid', $user->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findIncomingChallenges(User $user)
    {
        $qb = $this->createQueryBuilder('t');
        return $qb
            ->innerJoin('t.fighters', 'f')
            ->where('t.status = :status')
            ->andWhere($qb->expr()->isNull('t.adjudicate'))
            ->andWhere('f.id = :uid')
            ->setParameter('status', 'CREATED')
            ->setParameter('uid', $user->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNormalChallenges()
    {
        $qb = $this->createQueryBuilder('t');
        return $qb->where('t.status != :status')
            ->setParameter('status', 'CREATED')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Trial[] Returns an array of Trial objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Trial
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

<?php

namespace App\Repository;

use App\Entity\TwitchContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TwitchContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method TwitchContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method TwitchContent[]    findAll()
 * @method TwitchContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TwitchContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TwitchContent::class);
    }

    // /**
    //  * @return TwitchContent[] Returns an array of TwitchContent objects
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
    public function findOneBySomeField($value): ?TwitchContent
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

<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Ticket;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function findPastTickets()
    {
        $q = $this->createQueryBuilder('t')
            ->where('t.status != "CREATED"')
            ->getQuery();
        return $q->getResult();
    }

    public function findOpenTickets(User $user)
    {
        $qb = $this->createQueryBuilder('t');
        return $qb->where('t.createdBy = :user')
                ->andWhere($qb->expr()->in('t.status', array('CREATED', 'ACCEPTED')))
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();
    }

    // /**
    //  * @return Ticket[] Returns an array of Ticket objects
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
    public function findOneBySomeField($value): ?Ticket
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

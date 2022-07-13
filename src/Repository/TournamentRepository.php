<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Tournament;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Tournament|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tournament|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tournament[]    findAll()
 * @method Tournament[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournament::class);
    }

    public function findIncoming()
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(Tournament::class, 't');
        $sql = "SELECT " .  $rsm->generateSelectClause() . " FROM public.tournament AS t WHERE date_start > NOW() AND status='AWAITING' OR status='STARTED'";
        $query = $this->_em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function findAjudicatedTournaments(User $user)
    {
        $qb = $this->createQueryBuilder('tn');
        return $qb->innerJoin('tn.trials', 'tr')
            ->where('tn.createdBy = :uid')
            ->orWhere('tr.adjudicate = :user')
            ->andWhere($qb->expr()->in('tn.status', array('AWAITING', 'STARTED')))
            ->setParameters(['uid' => $user->getId(), 'user' => $user])
            ->orderBy('tn.dateStart', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Tournament[] Returns an array of Tournament objects
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
    public function findOneBySomeField($value): ?Tournament
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

<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Trial;
use App\Entity\Tournament;
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
        return $qb->innerJoin('t.fighters', 'f')
            ->where($qb->expr()->in('t.status',array("CREATED","DATE_ACCEPTED","DATE_REFUSED")))
            ->andWhere($qb->expr()->isNotNull('t.adjudicate'))
            ->andWhere($qb->expr()->isNull('t.tournament'))
            ->andWhere('f.id = :uid')
            ->setParameter('uid', $user->getId())
            ->getQuery()
            ->getResult()
        ;
    }
    public function findTrialsWithoutTournament()
    {
        $qb = $this->createQueryBuilder('t');
        return $qb->where($qb->expr()->isNull('t.tournament'))
            ->getQuery()
            ->getResult()
        ;
    }
    public function findTrialsWithTournament()
    {
        $qb = $this->createQueryBuilder('t');
        return $qb->where($qb->expr()->isNotNull('t.tournament'))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findIncomingChallenges(User $user)
    {
        $qb = $this->createQueryBuilder('t');
        return $qb->innerJoin('t.fighters', 'f')
            ->where($qb->expr()->in('t.status',array("CREATED")))
            ->andWhere($qb->expr()->isNull('t.adjudicate'))
            ->andWhere($qb->expr()->isNull('t.tournament'))
            ->andWhere('f.id = :uid')
            ->setParameter('uid', $user->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNormalChallenges(User $user)
    {
        $qb = $this->createQueryBuilder('t');
        return $qb->innerJoin('t.fighters', 'f')
            ->where($qb->expr()->notIn('t.status',array("CREATED","DATE_ACCEPTED","DATE_REFUSED","ACCEPTED","VALIDATED")))
            ->andWhere($qb->expr()->isNull('t.tournament'))
            ->andWhere('f.id = :uid')
            ->setParameter('uid', $user->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findChallenge(User $fighter, User $target)
    {
        $qb = $this->createQueryBuilder('t');
        return $qb->innerJoin('t.fighters', 'f1')
            ->innerJoin('t.fighters', 'f2')
            ->where($qb->expr()->in('t.status',array("CREATED","ACCEPTED","VALIDATED")))
            ->andWhere($qb->expr()->isNull('t.adjudicate'))
            ->andWhere($qb->expr()->isNull('t.tournament'))
            ->andWhere('(f1.id = :uid1 AND f2.id = :uid2) OR (f1.id = :uid2 AND f2.id = :uid1)')
            ->setParameter('uid1', $fighter->getId())
            ->setParameter('uid2', $target->getId())
            ->getQuery()
            ->getResult()    
        ;
    }

    public function findNotFullFighterTrial(Tournament $tournament)
    {
        $qb = $this->createQueryBuilder('trial');
        return $qb->innerJoin('trial.tournament', 'tournament')
            ->innerJoin('trial.fighters', 'fighters')
            ->where('tournament.id = :tid')
            ->andWhere('trial.tournamentStep = :step')
            ->andWhere("trial.status = :status")
            ->setParameter('tid', $tournament->getId())
            ->setParameter('step', 1)
            ->setParameter('status', "ENDED")
            ->getQuery()
            ->getResult()
            ;
    }

    public function findBetTrialForUser(Trial $trial, User $user)
    {
        $qb = $this->createQueryBuilder('tr');
        return $qb->innerJoin('tr.bets', 'b')
            ->where('tr.id = :tid')
            ->andWhere('b.better = :user')
            ->setParameters(['tid' => $trial->getId(), 'user' => $user])
            ->getQuery()
            ->getResult();
    }

    public function findStepOpenedTrialsForTournament(Tournament $tournament)
    {
        $qb = $this->createQueryBuilder('tr');
        return $qb->innerJoin('tr.tournament', 'tn')
            ->where('tn.id = :tournamentId')
            ->andWhere('tr.tournamentStep = tn.step')
            ->andWhere($qb->expr()->in('tr.status',array("STARTED","AWAITING")))
            ->setParameter('tournamentId',$tournament->getId())
            ->getQuery()
            ->getResult();
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

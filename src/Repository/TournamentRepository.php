<?php

namespace App\Repository;

use App\Entity\Tournament;
use App\Entity\Trial;
use App\Service\Type\ArrayService;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Tournament::class, 't');
        $sql = "SELECT " .  $rsm->generateSelectClause() . " FROM public.tournament AS t WHERE date_start > NOW() AND status='AWAITING' OR status='STARTED'";
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function createTrialsForTournament(Tournament $tournament): ?Tournament //2n²
    {
        if(count($tournament->getParticipantFromRole("ROLE_FIGHTER")) !== $tournament->getNbParticipants() 
        || count($tournament->getParticipantFromRole("ROLE_ADJUDICATE")) < (count($tournament->getParticipantFromRole("ROLE_FIGHTER"))/2) 
        || $tournament->getNbParticipants()%4 !== 0){
            return null;
        }

        $manager = $this->getEntityManager();

        $fighters = $tournament->getParticipantFromRole("ROLE_FIGHTER");
        $adjudicates = $tournament->getParticipantFromRole("ROLE_ADJUDICATE");
        $nbTrials = count($tournament->getParticipantFromRole("ROLE_FIGHTER")) / 2;

        $createdTrials = [];

        for($i=0; $i<$nbTrials;$i++){
            $object = (new Trial())
                ->addFighter(ArrayService::getRandomElem($fighters))
                ->addFighter(ArrayService::getRandomElem($fighters))
                ->setAdjudicate(ArrayService::getRandomElem($adjudicates))
                ->setTournament($tournament)
                ->setStatus("AWAITING")
                ->setCreatedBy($tournament->getCreatedBy());
            $lastTrials[] = $object;
            $manager->persist($object);
        }
        $adjudicates = $tournament->getParticipantFromRole("ROLE_ADJUDICATE");
        $createdTrials = [];
        while($nbTrials !== 1){//ON ITERE POUR CREER DES MATCH JUSQU'A AVOIR CREER LE MATCH FINAL
            for($i=0; $i<$nbTrials/2; $i++){
                //ON CREER LES MATCH SUIVANT EN METTANT LES LASTTRIALS DESSUS 
                $object = (new Trial())
                    ->setTournament($tournament)
                    ->setAdjudicate(ArrayService::getRandomElem($adjudicates))
                    ->addLastTrial(ArrayService::getRandomElem($lastTrials))
                    ->addLastTrial(ArrayService::getRandomElem($lastTrials))
                    ->setCreatedBy($tournament->getCreatedBy());
                $manager->persist($object);
                $createdTrials [] = $object;
            }
            $lastTrials = $createdTrials;//ON MET LES MATCH QU ON VIENT DE CREER DANS UN TABLEAU
            $createdTrials = [];
            $nbTrials = $nbTrials/2;//ON RESET LE NOMBRE DE TRIALS CREER POUR CONTINUE DE BOUCLER
        }
        $manager->flush();
        return $tournament;
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

<?php

namespace App\Service;

use App\Entity\Trial;
use App\Entity\Tournament;
use Doctrine\ORM\EntityManagerInterface;

class TournamentService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createTrialsForTournament(Tournament $tournament): ?Tournament //2n²
    {
        if(count($tournament->getParticipantFromRole("ROLE_FIGHTER")) !== $tournament->getNbMaxParticipants()
            || count($tournament->getParticipantFromRole("ROLE_ADJUDICATE")) < (count($tournament->getParticipantFromRole("ROLE_FIGHTER"))/2)
            || $tournament->getNbMaxParticipants()%4 !== 0){
            return null;
        }

        $manager = $this->entityManager;

        $fighters = $tournament->getParticipantFromRole("ROLE_FIGHTER");
        $adjudicates = $tournament->getParticipantFromRole("ROLE_ADJUDICATE");
        $nbTrials = count($tournament->getParticipantFromRole("ROLE_FIGHTER")) / 2;

        for($i=0; $i<$nbTrials;$i++){
            $object = (new Trial())
                ->addFighter(UArray::getRandomElem($fighters))
                ->addFighter(UArray::getRandomElem($fighters))
                ->setAdjudicate(UArray::getRandomElem($adjudicates))
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
                    ->setAdjudicate(UArray::getRandomElem($adjudicates))
                    ->addLastTrial(UArray::getRandomElem($lastTrials))
                    ->addLastTrial(UArray::getRandomElem($lastTrials))
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
}
<?php

namespace App\Service;

use App\Entity\User;
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

    public function createTrialsForTournament(Tournament $tournament): ?Tournament //4n²
    {
        $manager = $this->entityManager;

        $fighters = $tournament->getParticipantFromRole("ROLE_FIGHTER");
        $baseAdjudicates = $tournament->getParticipantFromRole("ROLE_ADJUDICATE");
        $adjudicates = $baseAdjudicates;

        $nbTrials = $tournament->getNbMaxParticipants() / 2;
        $stepIndex = 1;
        $baseTrials = []; //CE SERONT LES MATCH CREER POUR LA PREMIERE LIGNE
        for ($i = 0; $i < $nbTrials; $i++) {

            $object = (new Trial())
                ->setAdjudicate(UArray::getRandomElem($baseAdjudicates))
                ->setTournament($tournament)
                ->setTournamentStep($stepIndex)
                ->setStatus("AWAITING")
                ->setBetStatus(1)
                ->setCreatedBy($tournament->getCreatedBy());
            $baseTrials[] = $object;
            $manager->persist($object);
        }
        for ($i = 0; $i < 2; $i++) {
            foreach ($baseTrials as $trial) {
                if ($fighters && count($fighters) > 0) {
                    $trial->addFighter(UArray::getRandomElem($fighters));
                }
            }
        }
        $stockedBaseTrials = $baseTrials;
        $alltrials = $stockedBaseTrials;
        $tmpTrials = []; //SERVIRA DE CONTENEUR DES MATCH QU'ON CREERA
        while ($nbTrials !== 1) { //ON ITERE POUR CREER DES MATCH JUSQU'A AVOIR CREER LE MATCH FINAL
            $stepIndex += 1;
            for ($i = 0; $i < $nbTrials / 2; $i++) {
                //ON CREER LES MATCH SUIVANT EN METTANT LES LASTTRIALS DESSUS
                $object = (new Trial())
                    ->setTournament($tournament)
                    ->setAdjudicate(UArray::getRandomElem($adjudicates))
                    ->addLastTrial(UArray::getRandomElem($baseTrials))
                    ->addLastTrial(UArray::getRandomElem($baseTrials))
                    ->setTournamentStep($stepIndex)
                    ->setCreatedBy($tournament->getCreatedBy());
                $manager->persist($object);
                $tmpTrials[] = $object;
            }
            $baseTrials = $tmpTrials; //ON MET LES MATCH QU ON VIENT DE CREER DANS UN TABLEA
            $alltrials = array_merge($alltrials, $tmpTrials);
            $tmpTrials = [];
            $nbTrials = $nbTrials / 2; //ON RESET LE NOMBRE DE TRIALS CREER POUR CONTINUE DE BOUCLER
        }

        foreach ($stockedBaseTrials as $trial) {
            if (count($trial->getFighters()) === 1) {
                $trial->getNextTrial()->addFighter($trial->getFighters()[0]);
                $trial->setWinner($trial->getFighters()[0]);
                $trial->setStatus("ENDED");
                $trial->setBetStatus(0);
            }
        }
        $manager->flush();
        return $tournament;
    }

    public function addToTrial(Tournament $tournament, User $user): bool
    {
        $trials = $this->entityManager->getRepository(Trial::class)->findNotFullFighterTrial($tournament);
        $trial = UArray::getRandomElem($trials);
        $tournament->addParticipant($user);
        $trial->getNextTrial()->removeFighter($trial->getNextTrial()->getFighters()[0]);
        $trial->addFighter($user);
        $trial->setStatus("AWAITING");
        $trial->setWinner(null);
        $this->entityManager->flush();
        return true;
    }
}

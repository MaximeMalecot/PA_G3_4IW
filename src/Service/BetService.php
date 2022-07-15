<?php

namespace App\Service;

use DateTime;
use Exception;
use App\Entity\Bet;
use App\Entity\User;
use App\Entity\Trial;
use App\Entity\Tournament;
use App\Repository\BetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class BetService
{

    public function __construct(private Security $security, private EntityManagerInterface $em, private BetRepository $betRepository)
    {
    }

    /**
     * @throws Exception
     */
    public function createBet(Bet $bet, Trial $trial = null, Tournament $tournament = null): void
    {
        // TODO: cleanup code
        $better = $this->security->getUser();
        if ($better->getCredits() < $bet->getAmount()) {
            throw new Exception("Vous ne pouvez pas parier plus que vous avez.");
        }
        if ($bet->getAmount() < 1) {
            throw new Exception("Vous ne pouvez pas parier moins de 1 crédit.");
        }
        if ($trial !== null) {
            if (!in_array($bet->getBettee(), $trial->getFighters()->toArray())) {
                throw new Exception("Ce combattant n'est pas dans ce match.");
            }
            if ($trial->getBetStatus() !== 1) {
                throw new Exception("Ce match est déjà terminé ou pas encore prêt pour recevoir des paris.");
            }
            foreach ($better->getBets() as $b) {
                if ($b->getTrial() === $trial) {
                    throw new Exception("Vous avez déjà parié sur ce match.");
                }
            }
            $bet->setTrial($trial);
        }
        if ($tournament !== null) {
            if ($tournament->getStatus() !== "AWAITING") {
                throw new Exception("Ce tournoi est déjà terminé ou pas encore prêt pour recevoir des paris.");
            }
            if (!in_array($bet->getBettee(), $tournament->getParticipantFromRole("ROLE_FIGHTER"))) {
                throw new Exception("Ce combattant n'est pas dans ce tournoi.");
            }
            foreach ($better->getBets() as $b) {
                if ($b->getTournament() === $tournament) {
                    throw new Exception("Vous avez déjà parié sur ce tournoi.");
                }
            }
            $bet->setTournament($tournament);
        }

        $bet->setBetter($better);
        $better->setCredits($better->getCredits() - $bet->getAmount());
        $bet->setCreatedAt(new DateTime());
        $bet->setUpdatedAt(new DateTime());

        $this->em->persist($bet);
        $this->em->flush();
    }

    public function closeBets(Trial $trial = null, Tournament $tournament = null): void
    {
        if ($trial !== null) {
            $winningBets = $this->betRepository->findTrialWinners($trial);
            $betters = count($trial->getBets());
            if (count($winningBets) === 0) {
                return;
            }
            $ratioWinningPoints = ($betters-count($winningBets) / count($winningBets));
            foreach ($winningBets as $winningBet) {
                $winningBet->getBetter()->setCredits($winningBet->getBetter()->getCredits() + ($winningBet->getAmount() * $ratioWinningPoints));
            }
            $this->em->flush();
            return;
        }
        if($tournament !== null){
            $winningBets = $this->betRepository->findTournamentWinners($tournament);
            $betters = count($tournament->getBets());
            if (count($winningBets) === 0) {
                return;
            }
            $ratioWinningPoints = ($betters / count($winningBets));
            foreach ($winningBets as $winningBet) {
                $winningBet->getBetter()->setCredits($winningBet->getBetter()->getCredits() + ($winningBet->getAmount() * $ratioWinningPoints));
            }
            $this->em->flush();
            return;
        }
    }
}

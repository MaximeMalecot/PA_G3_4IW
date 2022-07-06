<?php
namespace App\Service;

use App\Entity\Bet;
use App\Entity\Tournament;
use App\Entity\Trial;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Core\Security;

class BetService {

    public function __construct(private Security $security, private EntityManagerInterface $em) {
    }

    /**
     * @throws Exception
     */
    public function createBet(Bet $bet, Trial $trial = null, Tournament $tournament = null): void
    {
        // TODO: cleanup code
        $better = $this->security->getUser();
        if ($better === $bet->getBettee()) {
            throw new Exception('Tu ne peux pas parier sur toi-même.');
        }
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_ADJUDICATE')) {
            throw new Exception('Vous ne pouvez pas parier car vous avez un rôle trop élevé.');
        }
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
            if (in_array($better, $trial->getFighters()->toArray())) {
                throw new Exception("Vous faites partie du match, vous ne pouvez pas parier.");
            }
            if ($trial->getStatus() !== "AWAITING") {
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
            if (in_array($better, $tournament->getParticipantFromRole("ROLE_FIGHTER"))) {
                throw new Exception("Vous faites partie du tournoi, vous ne pouvez pas parier.");
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


}
<?php
namespace App\Service;

use App\Entity\Bet;
use App\Entity\Tournament;
use App\Entity\Trial;
use App\Entity\User;
use App\Repository\BetRepository;
use App\Repository\UserRepository;
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
    public function createBet(Bet $bet, Trial $trial = null, Tournament $tournament = null) {
        $better = $this->security->getUser();
        if ($better === $bet->getBettee()) {
            throw new Exception('Tu ne peux pas parier sur toi-même.');
        }
        if (($trial !== null && $trial->getStatus() !== "AWAITING")
            || ($tournament !== null && $tournament?->getStatus() !== "AWAITING")) {
            throw new Exception('Ce match ou ce tournoi est déjà terminé ou pas encore prêt pour recevoir des paris.');
        }
        if (in_array("ROLE_ADJUDICATE", $better->getRoles())) {
            throw new Exception('Vous ne pouvez pas parier car vous avez le rôle ROLE_ADJUDICATE');
        }
        if ($trial !== null && !in_array($bet->getBettee(), $trial->getFighters()->toArray())) {
            throw new Exception("Ce combattant n'est pas dans ce match.");
        }
        if ($tournament !== null && !in_array($bet->getBettee(), $tournament->getParticipantFromRole("ROLE_FIGHTER"))) {
            throw new Exception("Ce combattant n'est pas dans ce tournoi.");
        }
        if ($better->getCredits() < $bet->getAmount()) {
            throw new Exception("Vous ne pouvez pas parier plus que vous avez.");
        }
        if ($bet->getAmount() < 1) {
            throw new Exception("Vous ne pouvez pas parier moins de 1 crédit.");
        }
        foreach ($better->getBets() as $b) {
            if ( $trial !== null && $b->getTrial() === $trial) {
                throw new Exception('Tu as déjà parié sur ce combat.');
            }
            if ($tournament !== null && $b->getTournament() === $tournament) {
                throw new Exception('Tu as déjà parié sur ce tournoi.');
            }
        }

        $bet->setBetter($better);
        $better->setCredits($better->getCredits() - $bet->getAmount());
        if ($trial) $bet->setTrial($trial);
        if ($tournament) $bet->setTournament($tournament);
        $bet->setCreatedAt(new DateTime());
        $bet->setUpdatedAt(new DateTime());

        $this->em->persist($bet);
        $this->em->flush();
    }


}
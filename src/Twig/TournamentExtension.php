<?php

namespace App\Twig;

use App\Entity\User;
use Twig\TwigFunction;
use App\Entity\Tournament;
use Twig\Extension\AbstractExtension;
use App\Repository\TournamentRepository;

class TournamentExtension extends AbstractExtension
{
    public function __construct(protected TournamentRepository $tournamentRepository)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getNbFromRole', [$this, 'getNbFromRole']),
            new TwigFunction('isInTournament', [$this, 'isInTournament']),
            new TwigFunction('canStartTournament', [$this, 'canStartTournament']),
            new TwigFunction('canLock', [$this, 'canLock']),
            new TwigFunction('canJoin', [$this, 'canJoin']),
            new TwigFunction('canQuit', [$this, 'canQuit']),
            new TwigFunction('canBetTournament', [$this, 'canBetTournament'])
        ];
    }

    public function getNbFromRole(Tournament $tournament, string $role): int
    {
        return count($tournament->getParticipantFromRole($role));
    }

    public function isInTournament(Tournament $tournament, User $user): bool
    {
        return $tournament->getParticipants()->contains($user);
    }

    public function canStartTournament(Tournament $tournament, User $user): bool
    {
        if ($tournament->getCreatedBy() === $user && $tournament->getStep() === 0 && count($tournament->getTrials()) > 0) {
            $startDate = \DateTime::createFromInterface($tournament->getDateStart());
            $now = new \DateTime("now", new \DateTimeZone('Europe/Paris'));
            $now = new \DateTime($now->format('Y-m-d H:i:s'));
            $min = clone $startDate;
            $max = clone $startDate;
            $min->sub(new \DateInterval("PT1H"));
            $max->add(new \DateInterval("PT1H"));
            if ($min < $now &&  $max > $now) {
                return true;
            }
        }
        return false;
    }
    public function canLock(Tournament $tournament, User $user): bool
    {
        return $tournament->getCreatedBy() === $user && $tournament->getStatus() === "CREATED" && count($tournament->getParticipantFromRole("ROLE_ADJUDICATE")) === $tournament->getNbMaxParticipants() / 2 && count($tournament->getParticipantFromRole("ROLE_FIGHTER")) > ($tournament->getNbMaxParticipants() / 2);
    }

    public function canJoin(Tournament $tournament, User $user): bool
    {
        if (in_array('ROLE_FIGHTER', $user->getRoles())) {
            return ($tournament->getStatus() === "CREATED" || $tournament->getStatus() === "AWAITING") &&
                !$tournament->getParticipants()->contains($user) &&
                (count($tournament->getParticipantFromRole("ROLE_FIGHTER")) < $tournament->getNbMaxParticipants());
        } else if (in_array('ROLE_ADJUDICATE', $user->getRoles())) {
            return $tournament->getStatus() === "CREATED" && !$tournament->getParticipants()->contains($user) && (count($tournament->getParticipantFromRole("ROLE_ADJUDICATE")) < ($tournament->getNbMaxParticipants() / 2));
        } else {
            return false;
        }
    }

    public function canQuit(Tournament $tournament, User $user): bool
    {
        return $tournament->getStatus() === "CREATED" && $tournament->getParticipants()->contains($user);
    }

    public function canBetTournament(Tournament $tournament, User $user): bool
    {
        return count($this->tournamentRepository->findBetTournamentForUser($tournament, $user)) === 0 &&
            $tournament->getStatus() === "AWAITING" &&
            !$tournament->getParticipants()->contains($user) &&
            (!in_array("ROLE_ADJUDICATE", $user->getRoles()) &&
                !in_array("ROLE_ADMIN", $user->getRoles()));
    }
}

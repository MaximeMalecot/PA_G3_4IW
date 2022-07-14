<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Tournament;
use App\Repository\TournamentRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TournamentVoter extends Voter
{
    /*
        TO IMPLEMENT THE VOTER IN A CONTROLLER JUST DO :
        #[IsGranted(UserVoter::EDIT, 'user')]
    */
    const CREATE = 'create';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const SHOW = 'show';
    const JOIN = 'join';
    const QUIT = 'quit';
    const LOCK = 'lock';
    const START = 'start';
    const BET = 'bet';

    public function __construct(private TournamentRepository $tournamentRepository)
    {
        
    }

    protected function supports(string $attribute, $subject): bool
    {
        if(in_array($attribute, [self::BET, self::CREATE, self::EDIT, self::DELETE, self::SHOW, self::JOIN, self::QUIT, self::LOCK, self::START])){
            if($attribute == self::CREATE){
                return true;
            } else {
                return $subject instanceof Tournament;
            }
        } 
        return false;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::JOIN:
                return $this->canJoin($subject, $user);
                break;
            case self::QUIT:
                return $this->canQuit($subject, $user);
                break;
            case self::SHOW:
                return $this->canShow($subject, $user);
                break;
            case self::CREATE:
                return in_array('ROLE_ADJUDICATE', $user->getRoles());
                break;
            case self::LOCK:
                return $subject->getCreatedBy() === $user && count($subject->getParticipantFromRole("ROLE_ADJUDICATE")) === $subject->getNbMaxParticipants() / 2 && count($subject->getParticipantFromRole("ROLE_FIGHTER")) > ($subject->getNbMaxParticipants() / 2 );
                break;
            case self::START:
                return $subject->getCreatedBy() === $user && $subject->getStep() === 0 && count($subject->getTrials()) > 0;
                break;
            case self::EDIT:
                return $this->canEdit($subject, $user);
                break;
            case self::BET:
                return $this->canBet($subject, $user);
                break;
            case self::DELETE:
                return in_array('ROLE_ADMIN', $user->getRoles()) || $this->canEdit($subject, $user);
                break;
        }

        return false;
    }

    protected function canJoin(Tournament $tournament, User $user) : bool 
    {
        if(in_array('ROLE_FIGHTER', $user->getRoles())){
            return ($tournament->getStatus() === "CREATED" || $tournament->getStatus() === "AWAITING") && 
            !$tournament->getParticipants()->contains($user) && 
            (count($tournament->getParticipantFromRole("ROLE_FIGHTER")) < $tournament->getNbMaxParticipants());
        } else if (in_array('ROLE_ADJUDICATE', $user->getRoles())) {
            return $tournament->getStatus() === "CREATED" && !$tournament->getParticipants()->contains($user) && (count($tournament->getParticipantFromRole("ROLE_ADJUDICATE")) < ($tournament->getNbMaxParticipants()/2));
        } else {
            return false;
        } 
    }

    protected function canBet(Tournament $tournament, User $user) : bool 
    {
        return count($this->tournamentRepository->findBetTournamentForUser($tournament, $user)) === 0 && 
        $tournament->getStatus() === "AWAITING" && 
        !$tournament->getParticipants()->contains($user) && 
        (in_array("ROLE_USER", $user->getRoles()) || 
        in_array("ROLE_FIGHTER", $user->getRoles()));
    }

    protected function canQuit(Tournament $tournament, User $user): bool
    {
        return $tournament->getStatus() === "CREATED" && $tournament->getParticipants()->contains($user);
    }

    protected function canShow(Tournament $tournament, User $user): bool 
    {
        return in_array("ROLE_ADJUDICATE", $user->getRoles()) || 
        in_array("ROLE_ADMIN", $user->getRoles()) ||
        in_array($tournament->getStatus(), ["AWAITING","STARTED","ENDED"]) ? true : in_array('ROLE_FIGHTER', $user->getRoles());
    }

    /**
     * @param Tournament $tournament
     * @param User $user
     * @return bool
     */
    protected function canEdit(Tournament $tournament, User $user): bool
    {
        return $tournament->getCreatedBy() == $user;
    }
}
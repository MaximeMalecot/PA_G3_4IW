<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Tournament;
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
    const START = 'start';

    protected function supports(string $attribute, $subject): bool
    {
        if(in_array($attribute, [self::CREATE, self::EDIT, self::DELETE, self::SHOW, self::JOIN, self::QUIT, self::START])){
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
            
            case self::START:
                return $subject->getCreatedBy() === $user;
                break;
            case self::EDIT:
                return $this->canEdit($subject, $user);
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
            return $tournament->getStatus() === "CREATED" && !$tournament->getParticipants()->contains($user) && (count($tournament->getParticipantFromRole("ROLE_FIGHTER")) < $tournament->getNbMaxParticipants());
        } else if (in_array('ROLE_ADJUDICATE', $user->getRoles())) {
            return $tournament->getStatus() === "CREATED" && !$tournament->getParticipants()->contains($user) && (count($tournament->getParticipantFromRole("ROLE_ADJUDICATE")) < ($tournament->getNbMaxParticipants()/2));
        } else {
            return false;
        } 
    }

    protected function canQuit(Tournament $tournament, User $user): bool
    {
        return $tournament->getStatus() === "CREATED" && $tournament->getParticipants()->contains($user);
    }

    protected function canShow(Tournament $tournament, User $user): bool 
    {
        return in_array($tournament->getStatus(), ["AWAITING","STARTED","ENDED"]) ? true : in_array('ROLE_FIGHTER', $user->getRoles());
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
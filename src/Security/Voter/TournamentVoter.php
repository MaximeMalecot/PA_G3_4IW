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

    protected function supports(string $attribute, $subject): bool
    {
        if(in_array($attribute, [self::CREATE, self::EDIT, self::DELETE])){
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
            case self::CREATE:
                return in_array('ROLE_ADJUDICATE', $user->getRoles());
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
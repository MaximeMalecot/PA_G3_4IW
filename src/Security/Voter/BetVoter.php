<?php

namespace App\Security\Voter;

use App\Entity\Bet;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class BetVoter extends Voter
{
    /*
        TO IMPLEMENT THE VOTER IN A CONTROLLER JUST DO :
        #[IsGranted(UserVoter::EDIT, 'user')]
    */
    const SHOW = 'show';
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::SHOW, self::EDIT, self::DELETE])
            && $subject instanceof Bet;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute){
            case self::SHOW:
                return $subject->getBetter() === $user || in_array("ROLE_ADMIN", $user->getRoles());
                break;
            case self::EDIT:
                return $subject->getBetter() === $user;
                break;   
            case self::DELETE:
                return $subject->getBetter() === $user || in_array("ROLE_ADMIN", $user->getRoles());
                break;
        }
    }
}
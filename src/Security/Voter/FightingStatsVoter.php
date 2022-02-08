<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\FightingStats;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class FightingStatsVoter extends Voter
{
    /*
        TO IMPLEMENT THE VOTER IN A CONTROLLER JUST DO :
        #[IsGranted(UserVoter::EDIT, 'user')]
    */
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof FightingStats;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_ADJUDICATE', $user->getRoles());
                break;
            case self::DELETE:
                return in_array('ROLE_ADMIN', $user->getRoles());
                break;
        }

        return false;
    }
}
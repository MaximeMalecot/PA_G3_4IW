<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    /*
        TO IMPLEMENT THE VOTER IN A CONTROLLER JUST DO :
        #[IsGranted(UserVoter::EDIT, 'user')]
    */
    const DELETE = 'delete';
    const EDIT = 'edit';
    const SHOW = 'show';
    const UPGRADE = 'upgrade';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::SHOW, self::EDIT, self::DELETE, self::UPGRADE])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        switch ($attribute) {
            case self::DELETE:
                return $this->canManage($subject, $user);
                break;
            case self::EDIT:
                return $this->canManage($subject, $user);
                break;
            case self::SHOW:
                return in_array('ROLE_FIGHTER', $subject->getRoles()) || $this->canManage($subject, $user);
                break;
            case self::UPGRADE:
                return $subject === $user;
                break;
        }

        return false;
    }

    /**
     * @param User $target
     * @param User $user
     * @return bool
     */
    protected function canManage(User $target, User $user): bool
    {
        return ($target === $user || in_array('ROLE_ADMIN', $user->getRoles()));
    }
}
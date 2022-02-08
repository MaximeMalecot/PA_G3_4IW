<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
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
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::SHOW:
                return $this->canManage($subject, $user) || in_array('ROLE_FIGHTER', $subject->getRoles());
                break;
            case self::EDIT:
                return $this->canManage($subject, $user);
                break;
            case self::DELETE:
                return $this->canManage($subject, $user);
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
        return ($target == $user || in_array('ROLE_ADMIN', $user->getRoles()));
    }
}
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
    const CREATE = 'create';
    const EDIT = 'edit';
    const DELETE = 'delete';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::SHOW, self::CREATE, self::EDIT, self::DELETE])
            && $subject instanceof Bet;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        if ($this->security->isGranted('ROLE_ADJUDICATE')) {
            return false;
        }

        return match ($attribute) {
            self::SHOW, self::EDIT, self::CREATE => $subject->getBetter() == $user,
            self::DELETE => $subject->getBetter() == $user || $this->security->isGranted("ROLE_ADMIN"),
            default => false,
        };

    }
}
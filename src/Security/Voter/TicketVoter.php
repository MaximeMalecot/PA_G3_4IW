<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Ticket;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TicketVoter extends Voter
{
    /*
        TO IMPLEMENT THE VOTER IN A CONTROLLER JUST DO :
        #[IsGranted(InvoiceVoter::EDIT, 'user')]
    */
    const SHOW = 'show';
    const NEW = 'new';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::SHOW])
            && $subject instanceof Ticket;
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
                return $subject->getBuyer()->getId() == $user->getId() ;
                break;
        }

        return false;
    }
}
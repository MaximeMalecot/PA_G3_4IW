<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TicketVoter extends Voter
{
    private $ticketRepository;

    const SHOW = 'show';
    const NEW = 'new';

    public function __construct(TicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if(in_array($attribute, [self::SHOW, self::NEW])){
            if($attribute == self::NEW){
                return true;
            } else {
                return $subject instanceof Ticket;
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
            case self::SHOW:
                return $subject->getBuyer()->getId() == $user->getId() ;
                break;
            case self::NEW:
                return ((in_array('ROLE_USER', $user->getRoles()) || in_array('ROLE_FIGHTER', $user->getRoles())) && $this->ticketRepository->findOneBy(['status' => 'CREATED', 'createdBy' => $user]) === null);
                break;
        }

        return false;
    }
}
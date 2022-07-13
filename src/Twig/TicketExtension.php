<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\TicketRepository;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class TicketExtension extends AbstractExtension
{
    protected $ticketRepository;

    public function __construct(TicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('canCreateTicket', [$this, 'canCreateTicket']),
            new TwigFunction('canUpgradeTicket', [$this, 'canUpgradeTicket'])
        ];
    }

    public function canCreateTicket(User $user)
    {
        return (in_array('ROLE_USER', $user->getRoles()) ||
            in_array('ROLE_FIGHTER', $user->getRoles())) &&
            count($this->ticketRepository->findOpenTickets($user)) === 0;
    }

    public function canUpgradeTicket(User $user)
    {
        return $this->ticketRepository->findOneBy(['status' => 'ACCEPTED', 'createdBy' => $user]) !== null;
    }
}

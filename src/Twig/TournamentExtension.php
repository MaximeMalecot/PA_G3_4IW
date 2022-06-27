<?php

namespace App\Twig;

use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Entity\User;
use App\Entity\Tournament;
use Twig\Extension\AbstractExtension;

class TournamentExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [$this, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getNbFromRole', [$this, 'getNbFromRole']),
            new TwigFunction('isInTournament', [$this, 'isInTournament'])
        ];
    }

    public function getNbFromRole(Tournament $tournament, string $role){
        return count($tournament->getParticipantFromRole($role));
    }

    public function isInTournament(Tournament $tournament, User $user){
        return $tournament->getParticipants()->contains($user);
    }

}

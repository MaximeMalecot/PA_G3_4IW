<?php

namespace App\Twig;

use App\Entity\User;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Repository\TrialRepository;
use Twig\Extension\AbstractExtension;

class TrialExtension extends AbstractExtension
{
    protected $trialRepository;

    public function __construct(TrialRepository $trialRepository)
    {
        $this->trialRepository = $trialRepository;
    }

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
            new TwigFunction('canChallenge', [$this, 'canChallenge']),
        ];
    }

    public function canChallenge(User $currentUser, User $target)
    {
        if( $currentUser === $target){
            return false;
        }
        $challenges = $this->trialRepository->findChallenge($currentUser, $target);
        if(!empty($challenges)){
            return false;
        }
        return true;
    }
}

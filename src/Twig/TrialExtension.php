<?php

namespace App\Twig;

use App\Entity\User;
use Twig\TwigFilter;
use App\Entity\Trial;
use Twig\TwigFunction;
use App\Repository\TrialRepository;
use DateTime;
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
            new TwigFunction('canStart', [$this, 'canStart']),
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

    public function canStart(User $user, Trial $trial)
    {
        $startDate = \DateTime::createFromInterface($trial->getDateStart());
        $now = new \DateTime("now", new \DateTimeZone('Europe/Paris'));
        $now = new \DateTime($now->format('Y-m-d H:i:s'));
        $min = clone $startDate;
        $max = clone $startDate;
        $min->sub(new \DateInterval("PT1H"));
        $min->add(new \DateInterval("P1D"));
        $max->add(new \DateInterval("PT1H"));
        $max->add(new \DateInterval("P1D"));
        if($trial->getAdjudicate() === $user && $min < $now &&  $max > $now){
            return true;
        }

        return false;
    }
}

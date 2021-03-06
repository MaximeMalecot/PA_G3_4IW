<?php

namespace App\Twig;

use App\Entity\User;
use App\Entity\Trial;
use Twig\TwigFunction;
use App\Repository\TrialRepository;
use Twig\Extension\AbstractExtension;

class TrialExtension extends AbstractExtension
{

    public function __construct(protected TrialRepository $trialRepository)
    {
        $this->trialRepository = $trialRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('canChallenge', [$this, 'canChallenge']),
            new TwigFunction('canStart', [$this, 'canStart']),
            new TwigFunction('canBetTrial', [$this, 'canBetTrial']),
        ];
    }

    public function canChallenge(User $currentUser, User $target)
    {
        if ($currentUser === $target) {
            return false;
        }
        $challenges = $this->trialRepository->findChallenge($currentUser, $target);
        if (!empty($challenges)) {
            return false;
        }
        return true;
    }

    public function canStart(User $user, Trial $trial)
    {
        if ($trial->getAdjudicate() === $user) {
            $startDate = \DateTime::createFromInterface($trial->getDateStart());
            $now = new \DateTime("now", new \DateTimeZone('Europe/Paris'));
            $now = new \DateTime($now->format('Y-m-d H:i:s'));
            $min = clone $startDate;
            $max = clone $startDate;
            $min->sub(new \DateInterval("PT1H"));
            $min->add(new \DateInterval("P1D"));
            $max->add(new \DateInterval("PT1H"));
            $max->add(new \DateInterval("P1D"));
            if ($min < $now &&  $max > $now) {
                return true;
            }
        }
        return false;
    }

    public function canBetTrial(Trial $trial, User $user)
    {
        return count($this->trialRepository->findBetTrialForUser($trial, $user)) === 0 &&
            $trial->getBetStatus() === 1 &&
            !$trial->getFighters()->contains($user) &&
            (!in_array("ROLE_ADJUDICATE", $user->getRoles()) &&
            !in_array("ROLE_ADMIN", $user->getRoles()));
    }
}

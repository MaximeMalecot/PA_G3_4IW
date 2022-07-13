<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DateExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('limitRefuseDelete', [$this, 'limitRefuseDelete']),
        ];
    }

    public function limitRefuseDelete(\DateTime $value)
    {
        $limitDate = $value->modify('-1 day');
        return $limitDate;
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Trial;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TrialFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /////////CLASSICTRIAL/////////


        /////////TOURNAMENTTRIAL/////////
    }

    public function getDependencies()
    {
        return [
            TournamentFixtures::class,
            UserFixtures::class
        ];
    }
}
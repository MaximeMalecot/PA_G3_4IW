<?php

namespace App\DataFixtures;

use App\Entity\Trial;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TournamentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /////////CLASSICTOURNAMENT/////////
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
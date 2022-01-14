<?php

namespace App\DataFixtures;

use App\Entity\Trial;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TrialFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /////////CLASSICTRIAL/////////
        $fighters = [];
        $adjudicates = [];
        foreach($manager->getRepository(User::class)->findByRole("ROLE_FIGHTER") as $fighter){
            $fighters[] = $manager->getRepository(User::class)->find($fighter->getId());
        }
        foreach($manager->getRepository(User::class)->findByRole("ROLE_ADJUDICATE") as $adjudicate){
            $adjudicates[] = $manager->getRepository(User::class)->find($adjudicate->getId());
        }
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
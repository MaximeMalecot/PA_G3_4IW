<?php

namespace App\DataFixtures;

use App\Entity\Tournament;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TournamentFixtures extends Fixture implements DependentFixtureInterface
{
    const TOURNAMENT_CREATED = 'tournament';
    public function load(ObjectManager $manager): void
    {
        /////////CLASSICTOURNAMENT/////////
       /* $fighters = [];
        foreach($manager->getRepository(User::class)->findByRole("ROLE_FIGHTER") as $fighter){
            $fighters[] = $manager->getRepository(User::class)->find($fighter->getId());
        }
        $obj = (new Tournament())
            ->setName("TrialHIHIHI")
            ->setParticipants(4);
        for($j=0; $j<4;$j++){
            $obj->addFighter($fighters[$j]);
        }
        $manager->persist($obj);
        $this->setReference(self::TOURNAMENT_CREATED, $obj);
        $manager->flush();*/
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
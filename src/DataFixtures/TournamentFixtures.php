<?php

namespace App\DataFixtures;

use App\Entity\Tournament;
use App\Entity\User;
use App\Utils\UArray;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TournamentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        /////////CLASSICTOURNAMENT/////////
        $fighters = $manager->getRepository(User::class)->findByRole("ROLE_FIGHTER");
        $adjudicates = $manager->getRepository(User::class)->findByRole("ROLE_ADJUDICATE");
        $tournaments = [];
        for($i=0; $i<2; $i++){
            $object = (new Tournament())
                ->setName($faker->realText(99,1))
                ->setNbParticipants(8);
            for($i=0; $i<8; $i++){
                $object->addParticipant(UArray::getRandomElem($fighters));
            }
            for($i=0; $i<4; $i++){
                $object->addParticipant(UArray::getRandomElem($adjudicates));
            }
            $manager->persist($object);
            $tournaments[] = $object;
        }
        foreach($tournaments as $tournament){
            $manager->getRepository(Tournament::class)->createTrialsForTournament($tournament);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
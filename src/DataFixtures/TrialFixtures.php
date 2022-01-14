<?php

namespace App\DataFixtures;

use App\Entity\Tournament;
use App\Entity\Trial;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TrialFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /////////TOURNAMENTTRIAL/////////
        /*$tournament = $this->getReference(TournamentFixtures::TOURNAMENT_CREATED);
        $fighters = $tournament->getFighters();
        for($i=0;$i<count($fighters)/2;$i+2){
            $object = (new Trial())
                ->addFighter($fighters[$i])
                ->addFighter($fighters[$i+1])
                ->setAdjudicate($adjudicates[$i%count($adjudicates)]);
            $manager->persist($object);
        }
        /*$trials = $manager->getRepository(Trial::class)->findAll();
        $tournaments = $manager->getRepository(Tournament::class)->findAll();
        $j=0;
        for($i=0; $i<count($trials)/2; $i++){
            $trial[$i];
            $j++;
        }*/
        /////////CLASSICTRIAL/////////
        /*$fighters = [];
        $adjudicates = [];
        foreach($manager->getRepository(User::class)->findByRole("ROLE_FIGHTER") as $fighter){
            $fighters[] = $manager->getRepository(User::class)->find($fighter->getId());
        }
        foreach($manager->getRepository(User::class)->findByRole("ROLE_ADJUDICATE") as $adjudicate){
            $adjudicates[] = $manager->getRepository(User::class)->find($adjudicate->getId());
        }
        for($i=0; $i<$fighters; $i+2){
            $object = (new Trial())
                ->addFighter($fighters[$i])
                ->addFighter($fighters[$i+1])
                ->setAdjudicate($adjudicates[$i%count($adjudicates)]);
            $manager->persist($object);
        }
        $manager->flush();*/
        
    }

    public function getDependencies()
    {
        return [
            TournamentFixtures::class,
            UserFixtures::class
        ];
    }
}
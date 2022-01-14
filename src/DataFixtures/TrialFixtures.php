<?php

namespace App\DataFixtures;

use App\Entity\Trial;
use App\Entity\User;
use App\Utils\UArray;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TrialFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        /////////CLASSICTRIAL/////////
        $fighters = $manager->getRepository(User::class)->findByRole("ROLE_FIGHTER");
        $adjudicates = $manager->getRepository(User::class)->findByRole("ROLE_ADJUDICATE");
        for($i=0; $i<5; $i++){
            $object = (new Trial())
                ->addFighter(UArray::getRandomElem($fighters))
                ->addFighter(UArray::getRandomElem($fighters))
                ->setAdjudicate(UArray::getRandomElem($adjudicates));
            $manager->persist($object);
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
<?php

namespace App\DataFixtures;

use App\Entity\Trial;
use App\Entity\User;
use App\Service\Type\ArrayService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TrialFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        /////////CLASSICTRIAL/////////
        $fighters = $manager->getRepository(User::class)->findByRole("ROLE_FIGHTER");
        $adjudicates = $manager->getRepository(User::class)->findByRole("ROLE_ADJUDICATE");
        for($i=0; $i<5; $i++){
            $object = (new Trial())
                ->addFighter(ArrayService::getRandomElem($fighters))
                ->addFighter(ArrayService::getRandomElem($fighters))
                ->setAdjudicate(ArrayService::getRandomElem($adjudicates))
                ->setStatus("AWAITING")
                ->setDateStart($faker->dateTimeBetween('+1 month', '+3 month'));
            $object->setCreatedBy($object->getAdjudicate());
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
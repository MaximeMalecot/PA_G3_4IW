<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Trial;
use App\Service\UArray;
use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TrialFixtures extends Fixture implements DependentFixtureInterface
{

    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        /////////CLASSICTRIAL/////////
        $fighters = $this->userRepository->findByRole("ROLE_FIGHTER");
        $adjudicates = $this->userRepository->findByRole("ROLE_ADJUDICATE");
        for($i=0; $i<5; $i++){
            $object = (new Trial())
                ->addFighter(UArray::getRandomElem($fighters))
                ->addFighter(UArray::getRandomElem($fighters))
                ->setAdjudicate(UArray::getRandomElem($adjudicates))
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
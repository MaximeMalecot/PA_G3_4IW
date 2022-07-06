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
        $adjudicateMain = $this->userRepository->findBy(["email" => "adjudicate0@adjudicate.com"],null,1);
        $fighters = $this->userRepository->findByRole("ROLE_FIGHTER");
        $adjudicates = $this->userRepository->findByRole("ROLE_ADJUDICATE");
        for($i=0; $i<5; $i++){
            $fighter1 = $faker->randomElement($fighters);
            $fighter2 = $faker->randomElement($fighters);
            $adjudicate = $faker->randomElement($adjudicates);
            $object = (new Trial())
                ->addFighter($fighter1)
                ->addFighter($fighter2)
                ->setAdjudicate($adjudicate)
                ->setStatus("AWAITING")
                ->setDateStart($faker->dateTimeBetween('+1 month', '+3 month'));
            $object->setCreatedBy($object->getAdjudicate());
            $manager->persist($object);
        }
        for($i=0; $i<3; $i++){
            $object = (new Trial())
                ->addFighter(UArray::getRandomElem($fighters))
                ->addFighter(UArray::getRandomElem($fighters))
                ->setAdjudicate($adjudicateMain[0])
                ->setStatus("AWAITING")
                ->setDateStart($faker->dateTimeBetween('+1 month', '+3 month'));
            $object->setCreatedBy($object->getAdjudicate());
            $manager->persist($object);
        }
        $manager->flush();
        $trials = $manager->getRepository(Trial::class)->findAll();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
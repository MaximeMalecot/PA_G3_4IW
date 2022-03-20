<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Bet;
use App\Entity\User;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\TrialFixtures;
use App\DataFixtures\InvoiceFixtures;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\TournamentFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BetFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // BET CLASSIQUE (TRIAL)
        $faker = Factory::create();
        $users = $manager->getRepository(User::class)->findAll();
//        $trials = $manager->getRepository(Trial::class)->findAll();

        for ($i = 0; $i<100; $i++) {
            $user = $faker->randomElement($users);
//            $trial = $faker->randomElement($trials);
            $object = (new Bet())
                ->setAmount($faker->numberBetween(1, $user->getCredits()))
            ->setBetter($user);

//            $trial->addBet($object);
            $user->addBet($object);
            $manager->persist($object);
        }

        // BET TOURNAMENT

        $manager->flush();


    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            TrialFixtures::class,
            TournamentFixtures::class,
            InvoiceFixtures::class
        ];
    }
}

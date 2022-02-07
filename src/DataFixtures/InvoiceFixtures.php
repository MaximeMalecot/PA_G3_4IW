<?php

namespace App\DataFixtures;

use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class InvoiceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Pour le moment, on va dire: 1$ = 100 cents = 100 crédits.
        // TODO: se mettre d'accord sur le prix final des crédits avec tout le monde.
        // TODO: et changer le type de price en float : https://developer.paypal.com/api/payments/v2/#definition-money

        $faker = Factory::create();
        $users = $manager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $priceInDollars = $faker->numberBetween(1, 1000);


            $object = (new Invoice())
                ->setCreditAmount($priceInDollars * 100)
                ->setPrice($priceInDollars * 100)
                ->setIdPaypal($faker->uuid());

            $user->addInvoice($object);
            $user->setCredits($user->getCredits() + $priceInDollars * 100);

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

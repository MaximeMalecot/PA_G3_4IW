<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture 
{

    /** @var UserPasswordHasherInterface $userPasswordHasher */
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        /////////ADMIN/////////
        $admin = (new User())
            ->setEmail('admin@admin.com')
            ->setIsVerified(true)
            ->setRoles(['ROLE_ADMIN'])
            ->setNickname($faker->userName)
            ->setDescription($faker->realText(400,2))
        ;
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'test'));
        $manager->persist($admin);

        /////////ADJUDICATE/////////
        for($i=0;$i<40;$i++){
            $adjudicate = (new User())
                ->setEmail("adjudicate{$i}@adjudicate.com")
                ->setIsVerified(true)
                ->setRoles(['ROLE_ADJUDICATE'])
                ->setNickname($faker->userName)
                ->setDescription($faker->realText(400,2))
            ;
            $adjudicate->setPassword($this->userPasswordHasher->hashPassword($adjudicate, 'test'));
            $manager->persist($adjudicate);
        }

        /////////FIGHTER/////////

        for($i=0;$i<100;$i++){
            $fighter = (new User())
                ->setEmail("fighter{$i}@fighter.com")
                ->setIsVerified(true)
                ->setRoles(['ROLE_FIGHTER'])
                ->setNickname($faker->userName)
                ->setDescription($faker->realText(400,2))
            ;
            $fighter->setPassword($this->userPasswordHasher->hashPassword($fighter, 'test'));
            $manager->persist($fighter);
        }

        /////////USER/////////
        for($i=0;$i<5;$i++){
            $user = (new User())
                ->setEmail("user{$i}@user.com")
                ->setIsVerified(true)
                ->setRoles(['ROLE_USER'])
                ->setNickname($faker->userName)
            ;
            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'test'));
            $manager->persist($user);
        }
        $manager->flush();
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture 
{
    const USER_ADMIN = 'admin';
    const USER_ADJUDICATE = 'adjudicate';
    const USER_FIGHTER = 'fighter';
    const USER_USER = 'user';

    /** @var UserPasswordHasherInterface $userPasswordHasher */
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        /////////ADMIN/////////
        $admin = (new User())
            ->setEmail('admin@admin')
            ->setIsVerified(true)
            ->setRoles(['ROLE_ADMIN'])
            ->setNickname('adminTest')
            ->setDescription('JE SUIS LE SUPER ADMIN')
        ;
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'test'));
        $manager->persist($admin);
        $this->setReference(self::USER_ADMIN, $admin);

        /////////ADJUDICATE/////////
        for($i=0;$i<5;$i++){
            $adjudicate = (new User())
                ->setEmail("adjudicate{$i}@adjudicate")
                ->setIsVerified(true)
                ->setRoles(['ROLE_ADJUDICATE'])
                ->setNickname("adjudicateTest{$i}")
                ->setDescription('JE SUIS LE SUPER ADJUDICATE')
            ;
            $adjudicate->setPassword($this->userPasswordHasher->hashPassword($adjudicate, 'test'));
            $manager->persist($adjudicate);
        }
        $this->setReference(self::USER_ADJUDICATE, $adjudicate);

        /////////FIGHTER/////////

        for($i=0;$i<20;$i++){
            $fighter = (new User())
                ->setEmail("fighter{$i}@fighter")
                ->setIsVerified(true)
                ->setRoles(['ROLE_FIGHTER'])
                ->setNickname("fighterTest{$i}")
                ->setDescription('JE SUIS LE SUPER FIGHTER')
            ;
            $fighter->setPassword($this->userPasswordHasher->hashPassword($fighter, 'test'));
            $manager->persist($fighter);
        }
        $this->setReference(self::USER_FIGHTER, $fighter);

        /////////USER/////////
        $user = (new User())
            ->setEmail('user@user')
            ->setIsVerified(true)
            ->setRoles(['ROLE_USER'])
            ->setNickname('userTest')
        ;
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'test'));
        $manager->persist($user);
        $this->setReference(self::USER_USER, $user);

        $manager->flush();
    }
}

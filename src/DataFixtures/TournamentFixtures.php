<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Service\UArray;
use App\Entity\Tournament;
use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use App\Repository\TournamentRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TournamentFixtures extends Fixture implements DependentFixtureInterface
{

    private $userRepository;
    private $tournamentRepository;

    public function __construct(UserRepository $userRepository, TournamentRepository $tournamentRepository)
    {
        $this->userRepository = $userRepository;
        $this->tournamentRepository = $tournamentRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        /////////CLASSICTOURNAMENT/////////
        $fighters = $this->userRepository->findByRole("ROLE_FIGHTER");
        $adjudicates = $this->userRepository->findByRole("ROLE_ADJUDICATE");
        $tournaments = [];
        for($i=0; $i<2; $i++){
            $object = (new Tournament())
                ->setName($faker->realText(99,1))
                ->setNbMaxParticipants(8)
                ->setDateStart($faker->dateTimeBetween('+1 month', '+3 month'))
                ->setStatus("AWAITING")
                ->setCreatedBy(UArray::getRandomElem($adjudicates));
            for($j=0; $j<8; $j++){
                $object->addParticipant(UArray::getRandomElem($fighters));
            }
            for($z=0; $z<4; $z++){
                $object->addParticipant(UArray::getRandomElem($adjudicates));
            }
            $manager->persist($object);
            $tournaments[] = $object;
        }
        foreach($tournaments as $tournament){
            $this->tournamentRepository->createTrialsForTournament($tournament);
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
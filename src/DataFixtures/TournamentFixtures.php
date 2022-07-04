<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Service\UArray;
use App\Entity\Tournament;
use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use App\Service\TournamentService;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TournamentFixtures extends Fixture implements DependentFixtureInterface
{

    private $userRepository;
    private $tournamentService;

    public function __construct(UserRepository $userRepository, TournamentService $tournamentService)
    {
        $this->userRepository = $userRepository;
        $this->tournamentService = $tournamentService;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        /////////CLASSICTOURNAMENT/////////
        $adjudicateMain = $this->userRepository->findBy(["email" => "adjudicate0@adjudicate.com"],null,1);
        $fighters = $this->userRepository->findByRole("ROLE_FIGHTER");
        $adjudicates = $this->userRepository->findByRole("ROLE_ADJUDICATE");
        $tournaments = [];
        for($i=0; $i<2; $i++){
            $object = (new Tournament())
                ->setName($faker->realText(99,1))
                ->setNbMaxParticipants(16)
                ->setDateStart($faker->dateTimeBetween('+1 month', '+3 month'))
                ->setStatus("AWAITING")
                ->setCreatedBy($adjudicateMain[0]);
            for($j=0; $j<16; $j++){
                $object->addParticipant(UArray::getRandomElem($fighters));
            }
            for($z=0; $z<8; $z++){
                $object->addParticipant(UArray::getRandomElem($adjudicates));
            }
            $manager->persist($object);
            $tournaments[] = $object;
        }
        foreach($tournaments as $tournament){
            $this->tournamentService->createTrialsForTournament($tournament);
        }

        $object = (new Tournament())
            ->setName($faker->realText(99,1))
            ->setNbMaxParticipants(8)
            ->setDateStart($faker->dateTimeBetween('+1 week', '+3 month'))
            ->setStatus("CREATED")
            ->setCreatedBy($adjudicateMain[0]);
        for($i=0; $i<7; $i++){
            if($i < 4){
                $object->addParticipant(UArray::getRandomElem($adjudicates));
            }
            $object->addParticipant(UArray::getRandomElem($fighters));
        }
        $manager->persist($object);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
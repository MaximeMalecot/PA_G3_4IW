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
        $adjudicateMain = $this->userRepository->findOneBy(["email" => "adjudicate0@adjudicate.com"]);
        $fighters = $this->userRepository->findByRole("ROLE_FIGHTER");
        $adjudicates = $this->userRepository->findByRole("ROLE_ADJUDICATE");
        $tournaments = [];
        for($i=0; $i<2; $i++){
            $object = (new Tournament())
                ->setName($faker->realText(99,1))
                ->setNbMaxParticipants(16)
                ->setDateStart($faker->dateTimeBetween('+1 month', '+3 month'))
                ->setStatus("AWAITING")
                ->setCreatedBy($adjudicateMain);
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
        $date = new \DateTime("now", new \DateTimeZone('Europe/Paris'));
        $date->add(new \DateInterval("PT1H"));
        $object = (new Tournament())
            ->setName($faker->realText(99,1))
            ->setNbMaxParticipants(4)
            ->setDateStart($date)
            ->setStatus("CREATED")
            ->setCreatedBy($adjudicateMain);
        for($i=0; $i<4; $i++){
            if($i < 2){
                $object->addParticipant($this->userRepository->findOneBy(["email" => "adjudicate".$i."@adjudicate.com"]));
            }
            $object->addParticipant($this->userRepository->findOneBy(["email" => "fighter".$i."@fighter.com"]));
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
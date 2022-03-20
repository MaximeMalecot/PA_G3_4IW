<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\FightingStats;
use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use App\Service\FightingStatsService;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class FightingStatsFixtures extends Fixture implements DependentFixtureInterface
{

    private $fightingStatsService;
    private $userRepository;

    public function __construct(FightingStatsService $fightingStatsService, UserRepository $userRepository)
    {
        $this->fightingStatsService = $fightingStatsService;
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $fighters = $this->userRepository->findByRole("ROLE_FIGHTER");
        foreach ($fighters as $fighter) {
            $object = (new FightingStats())
                ->setVictories($faker->numberBetween(0, 100))
                ->setDefeats($faker->numberBetween(0, 100))
                ->setRankingPoints($faker->randomDigit())
                ->setTarget($fighter);
            $this->fightingStatsService->placeRank($object);
            $fighter->setFightingStats($object);
            $manager->persist($object);
            $manager->flush();
        }
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\FightingStats;
use App\Entity\User;
use App\Service\FightingStatsService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FightingStatsFixtures extends Fixture implements DependentFixtureInterface
{

    private $fightingStatsService;

    public function __construct(FightingStatsService $fightingStatsService)
    {
        $this->fightingStatsService = $fightingStatsService;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $fighters = $manager->getRepository(User::class)->findByRole("ROLE_FIGHTER");
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

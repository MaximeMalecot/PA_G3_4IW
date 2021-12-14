<?php

namespace App\Entity;

use App\Repository\FightingStatsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FightingStatsRepository::class)
 */
class FightingStats
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $rankingPoints;

    /**
     * @ORM\Column(type="integer")
     */
    private $victories;

    /**
     * @ORM\Column(type="integer")
     */
    private $defeats;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRankingPoints(): ?int
    {
        return $this->rankingPoints;
    }

    public function setRankingPoints(int $rankingPoints): self
    {
        $this->rankingPoints = $rankingPoints;

        return $this;
    }

    public function getVictories(): ?int
    {
        return $this->victories;
    }

    public function setVictories(int $victories): self
    {
        $this->victories = $victories;

        return $this;
    }

    public function getDefeats(): ?int
    {
        return $this->defeats;
    }

    public function setDefeats(int $defeats): self
    {
        $this->defeats = $defeats;

        return $this;
    }
}

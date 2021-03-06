<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use App\Repository\FightingStatsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FightingStatsRepository::class)
 */
class FightingStats
{
    use TimestampableTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $rankingPoints = 0;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $victories = 0;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $defeats = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $rank;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="fightingStats", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $target;

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

    public function getTarget(): ?User
    {
        return $this->target;
    }

    public function setTarget(User $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

}

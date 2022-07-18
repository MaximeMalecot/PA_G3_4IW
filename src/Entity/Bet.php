<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\BetRepository;
use App\Entity\Traits\TimestampableTrait;

/**
 * @ORM\Entity(repositoryClass=BetRepository::class)
 */
class Bet
{
    use TimestampableTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="bets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $better;

    /**
     * @ORM\ManyToOne(targetEntity=Trial::class, inversedBy="bets")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    private $trial;

    /**
     * @ORM\ManyToOne(targetEntity=Tournament::class, inversedBy="bets")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    private $tournament;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="inverseBets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $bettee;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $victoryType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getBetter(): ?User
    {
        return $this->better;
    }

    public function setBetter(?User $better): self
    {
        $this->better = $better;

        return $this;
    }

    public function getTrial(): ?Trial
    {
        return $this->trial;
    }

    public function setTrial(?Trial $trial): self
    {
        $this->trial = $trial;

        return $this;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): self
    {
        $this->tournament = $tournament;

        return $this;
    }

    public function getBettee(): ?User
    {
        return $this->bettee;
    }

    public function setBettee(?User $bettee): self
    {
        $this->bettee = $bettee;

        return $this;
    }

    public function getVictoryType(): ?string
    {
        return $this->victoryType;
    }

    public function setVictoryType(?string $victoryType): self
    {
        $this->victoryType = $victoryType;

        return $this;
    }
}

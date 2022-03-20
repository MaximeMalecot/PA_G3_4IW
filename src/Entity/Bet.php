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
     */
    private $trial;

    /**
     * @ORM\ManyToOne(targetEntity=Tournament::class, inversedBy="bets")
     */
    private $tournament;

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
}

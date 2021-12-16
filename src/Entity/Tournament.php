<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TournamentRepository::class)
 */
class Tournament
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateEnd;

    /**
     * @ORM\Column(type="integer")
     */
    private $participants;

    /**
     * @ORM\OneToMany(targetEntity=Trial::class, mappedBy="tournament")
     */
    private $trials;

    /**
     * @ORM\OneToMany(targetEntity=Bet::class, mappedBy="tournament")
     */
    private $bets;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="tournaments")
     */
    private $fighters;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="wonTournaments")
     */
    private $winner;

    public function __construct()
    {
        $this->trials = new ArrayCollection();
        $this->bets = new ArrayCollection();
        $this->fighters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(?\DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?\DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getParticipants(): ?int
    {
        return $this->participants;
    }

    public function setParticipants(int $participants): self
    {
        $this->participants = $participants;

        return $this;
    }

    /**
     * @return Collection|Trial[]
     */
    public function getTrials(): Collection
    {
        return $this->trials;
    }

    public function addMatch(Trial $match): self
    {
        if (!$this->trials->contains($match)) {
            $this->trials[] = $match;
            $match->setTournament($this);
        }

        return $this;
    }

    public function removeMatch(Trial $match): self
    {
        if ($this->trials->removeElement($match)) {
            // set the owning side to null (unless already changed)
            if ($match->getTournament() === $this) {
                $match->setTournament(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Bet[]
     */
    public function getBets(): Collection
    {
        return $this->bets;
    }

    public function addBet(Bet $bet): self
    {
        if (!$this->bets->contains($bet)) {
            $this->bets[] = $bet;
            $bet->setTournament($this);
        }

        return $this;
    }

    public function removeBet(Bet $bet): self
    {
        if ($this->bets->removeElement($bet)) {
            // set the owning side to null (unless already changed)
            if ($bet->getTournament() === $this) {
                $bet->setTournament(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getFighters(): Collection
    {
        return $this->fighters;
    }

    public function addFighter(User $fighter): self
    {
        if (!$this->fighters->contains($fighter)) {
            $this->fighters[] = $fighter;
        }

        return $this;
    }

    public function removeFighter(User $fighter): self
    {
        $this->fighters->removeElement($fighter);

        return $this;
    }

    public function getWinner(): ?User
    {
        return $this->winner;
    }

    public function setWinner(?User $winner): self
    {
        $this->winner = $winner;

        return $this;
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TrialRepository;
use Doctrine\ORM\Mapping\JoinColumn;
use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity(repositoryClass=TrialRepository::class)
 */
class Trial
{
    use BlameableTrait;
    use TimestampableTrait;

    const ENUM_STATUS = ["CREATED","DATE_ACCEPTED","AWAITING","STARTED","ENDED","DATE_REFUSED","REFUSED"];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, options={"default": "CREATED"})
     */
    private $status="CREATED";

    /**
     * @ORM\Column(type="smallint")
     */
    private $betStatus = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateEnd;

    /**
     * @ORM\ManyToOne(targetEntity=Tournament::class, inversedBy="trials")
     */
    private $tournament;

    /**
     * @ORM\OneToMany(targetEntity=Bet::class, mappedBy="trial")
     */
    private $bets;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="adjudicatedTrials")
     * @JoinColumn(onDelete="SET NULL")
     */
    private $adjudicate;

    /**
     * @ORM\ManyToOne(targetEntity=Trial::class, inversedBy="lastTrials")
     */
    private $nextTrial;

    /**
     * @ORM\OneToMany(targetEntity=Trial::class, mappedBy="nextTrial")
     */
    private $lastTrials;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="fightingTrials")
     * @JoinColumn(onDelete="SET NULL")
     */
    private $fighters;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="wonTrials")
     * @JoinColumn(onDelete="SET NULL")
     */
    private $winner;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="acceptedTrials")
     * @JoinColumn(onDelete="SET NULL")
     */
    private $acceptedBy;

    public function __construct()
    {
        $this->trials = new ArrayCollection();
        $this->bets = new ArrayCollection();
        $this->lastTrials = new ArrayCollection();
        $this->fighters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, self::ENUM_STATUS)) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;

        return $this;
    }

    public function getBetStatus(): ?int
    {
        return $this->betStatus;
    }

    public function setBetStatus(int $betStatus): self
    {
        $this->betStatus = $betStatus;

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


    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): self
    {
        $this->tournament = $tournament;

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
            $bet->setTrial($this);
        }

        return $this;
    }

    public function removeBet(Bet $bet): self
    {
        if ($this->bets->removeElement($bet)) {
            // set the owning side to null (unless already changed)
            if ($bet->getTrial() === $this) {
                $bet->setTrial(null);
            }
        }

        return $this;
    }

    public function getAdjudicate(): ?User
    {
        return $this->adjudicate;
    }

    public function setAdjudicate(?User $adjudicate): self
    {
        $this->adjudicate = $adjudicate;

        return $this;
    }

    public function getNextTrial(): ?self
    {
        return $this->nextTrial;
    }

    public function setNextTrial(?self $nextTrial): self
    {
        $this->nextTrial = $nextTrial;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getLastTrials(): Collection
    {
        return $this->lastTrials;
    }

    public function addLastTrial(self $lastTrial): self
    {
        if (!$this->lastTrials->contains($lastTrial)) {
            $this->lastTrials[] = $lastTrial;
            $lastTrial->setNextTrial($this);
        }

        return $this;
    }

    public function removeLastTrial(self $lastTrial): self
    {
        if ($this->lastTrials->removeElement($lastTrial)) {
            // set the owning side to null (unless already changed)
            if ($lastTrial->getNextTrial() === $this) {
                $lastTrial->setNextTrial(null);
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

    public function getAcceptedBy(): ?User
    {
        return $this->acceptedBy;
    }

    public function setAcceptedBy(?User $acceptedBy): self
    {
        $this->acceptedBy = $acceptedBy;

        return $this;
    }
}

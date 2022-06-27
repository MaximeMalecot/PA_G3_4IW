<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\BlameableTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Repository\TournamentRepository;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TournamentRepository::class)
 */
class Tournament
{
    use BlameableTrait;
    use TimestampableTrait;
    
    const ENUM_STATUS = [ "CREATED","AWAITING","STARTED","ENDED"];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="name required")
     * @Assert\NotNull()
     * @Assert\Type(type="string",message="The value {{ value }} is not a valid {{ type }}.")
     * @Assert\Length(min = 2, max = 100,
     *      minMessage = "Name must be at least {{ limit }} characters long",
     *      maxMessage = "Name cannot be longer than {{ limit }} characters")
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
    private $nbMaxParticipants;

    /**
     * @ORM\Column(type="string", length=20, options={"default": "CREATED"})
     */
    private $status="CREATED";

    /**
     * @var string|null
     *
     * @Gedmo\Slug(fields={"id", "name"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity=Trial::class, mappedBy="tournament")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     */
    private $trials;

    /**
     * @ORM\OneToMany(targetEntity=Bet::class, mappedBy="tournament")
     */
    private $bets;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="tournaments")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="wonTournaments")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $winner;

    public function __construct()
    {
        $this->trials = new ArrayCollection();
        $this->bets = new ArrayCollection();
        $this->participants = new ArrayCollection();
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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

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

    public function getNbMaxParticipants(): ?int
    {
        return $this->nbMaxParticipants;
    }

    public function setNbMaxParticipants(int $nbMaxParticipants): self
    {
        $this->nbMaxParticipants = $nbMaxParticipants;

        return $this;
    }

    public function getSlug(): ?string 
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self 
    {
        $this->slug = $slug;
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
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getParticipantFromRole(string $role): array 
    {
        $participantsMatchingRole = [];
        foreach($this->participants as $participant){
            if(in_array($role,$participant->getRoles())){
                $participantsMatchingRole[] = $participant;
            }
        }
        return $participantsMatchingRole;
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
    
}

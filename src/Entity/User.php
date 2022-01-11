<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean", options={"default: true"})
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $nickname;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     */
    private $credits;

    /**
     * @ORM\OneToMany(targetEntity=Bet::class, mappedBy="better", orphanRemoval=true)
     */
    private $bets;

    /**
     * @ORM\OneToMany(targetEntity=Trial::class, mappedBy="adjudicate")
     */
    private $adjudicatedTrials;

    /**
     * @ORM\ManyToMany(targetEntity=Trial::class, mappedBy="fighters")
     */
    private $fightingTrials;

    /**
     * @ORM\ManyToMany(targetEntity=Tournament::class, mappedBy="fighters")
     */
    private $tournaments;

    /**
     * @ORM\OneToOne(targetEntity=FightingStats::class, mappedBy="target", cascade={"persist", "remove"})
     */
    private $fightingStats;

    /**
     * @ORM\OneToMany(targetEntity=Tournament::class, mappedBy="winner")
     */
    private $wonTournaments;

    /**
     * @ORM\OneToMany(targetEntity=Trial::class, mappedBy="winner")
     */
    private $wonTrials;

    /**
     * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="buyer", orphanRemoval=true)
     */
    private $invoices;

    public function __construct()
    {
        $this->bets = new ArrayCollection();
        $this->adjudicatedTrials = new ArrayCollection();
        $this->fightingTrials = new ArrayCollection();
        $this->tournaments = new ArrayCollection();
        $this->wonTournaments = new ArrayCollection();
        $this->wonTrials = new ArrayCollection();
        $this->invoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
            $bet->setBetter($this);
        }

        return $this;
    }

    public function removeBet(Bet $bet): self
    {
        if ($this->bets->removeElement($bet)) {
            // set the owning side to null (unless already changed)
            if ($bet->getBetter() === $this) {
                $bet->setBetter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Trial[]
     */
    public function getAdjudicatedTrials(): Collection
    {
        return $this->adjudicatedTrials;
    }

    public function addTrial(Trial $trial): self
    {
        if (!$this->adjudicatedTrials->contains($trial)) {
            $this->adjudicatedTrials[] = $trial;
            $trial->setAdjudicate($this);
        }

        return $this;
    }

    public function removeTrial(Trial $trial): self
    {
        if ($this->adjudicatedTrials->removeElement($trial)) {
            // set the owning side to null (unless already changed)
            if ($trial->getAdjudicate() === $this) {
                $trial->setAdjudicate(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Trial[]
     */
    public function getFightingTrials(): Collection
    {
        return $this->fightingTrials;
    }

    public function addFightingTrial(Trial $fightingTrial): self
    {
        if (!$this->fightingTrials->contains($fightingTrial)) {
            $this->fightingTrials[] = $fightingTrial;
            $fightingTrial->addFighter($this);
        }

        return $this;
    }

    public function removeFightingTrial(Trial $fightingTrial): self
    {
        if ($this->fightingTrials->removeElement($fightingTrial)) {
            $fightingTrial->removeFighter($this);
        }

        return $this;
    }

    /**
     * @return Collection|Tournament[]
     */
    public function getTournaments(): Collection
    {
        return $this->tournaments;
    }

    public function addTournament(Tournament $tournament): self
    {
        if (!$this->tournaments->contains($tournament)) {
            $this->tournaments[] = $tournament;
            $tournament->addFighter($this);
        }

        return $this;
    }

    public function removeTournament(Tournament $tournament): self
    {
        if ($this->tournaments->removeElement($tournament)) {
            $tournament->removeFighter($this);
        }

        return $this;
    }

    public function getFightingStats(): ?FightingStats
    {
        return $this->fightingStats;
    }

    public function setFightingStats(FightingStats $fightingStats): self
    {
        // set the owning side of the relation if necessary
        if ($fightingStats->getTarget() !== $this) {
            $fightingStats->setTarget($this);
        }

        $this->fightingStats = $fightingStats;

        return $this;
    }

    /**
     * @return Collection|Tournament[]
     */
    public function getWonTournaments(): Collection
    {
        return $this->wonTournaments;
    }

    public function addWonTournament(Tournament $wonTournament): self
    {
        if (!$this->wonTournaments->contains($wonTournament)) {
            $this->wonTournaments[] = $wonTournament;
            $wonTournament->setWinner($this);
        }

        return $this;
    }

    public function removeWonTournament(Tournament $wonTournament): self
    {
        if ($this->wonTournaments->removeElement($wonTournament)) {
            // set the owning side to null (unless already changed)
            if ($wonTournament->getWinner() === $this) {
                $wonTournament->setWinner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Trial[]
     */
    public function getWonTrials(): Collection
    {
        return $this->wonTrials;
    }

    public function addWonTrial(Trial $wonTrial): self
    {
        if (!$this->wonTrials->contains($wonTrial)) {
            $this->wonTrials[] = $wonTrial;
            $wonTrial->setWinner($this);
        }

        return $this;
    }

    public function removeWonTrial(Trial $wonTrial): self
    {
        if ($this->wonTrials->removeElement($wonTrial)) {
            // set the owning side to null (unless already changed)
            if ($wonTrial->getWinner() === $this) {
                $wonTrial->setWinner(null);
            }
        }

        return $this;
    }

    public function getCredits(): ?int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): self
    {
        $this->credits = $credits;

        return $this;
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setBuyer($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getBuyer() === $this) {
                $invoice->setBuyer(null);
            }
        }

        return $this;
    }
}

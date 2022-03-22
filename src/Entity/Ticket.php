<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TicketRepository;
use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\TimestampableTrait;

/**
 * @ORM\Entity(repositoryClass=TicketRepository::class)
 */
class Ticket
{
    use TimestampableTrait;
    use BlameableTrait;

    const ENUM_STATUS = ["CREATED","ACCEPTED","ENDED","REFUSED"];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    
    /**
     * @ORM\Column(type="string", length=50)
     */
    private $roleWanted;
    
    /**
     * @ORM\Column(type="string", length=20, options={"default": "CREATED"})
     */
    private $status="CREATED";


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getRoleWanted(): ?string
    {
        return $this->roleWanted;
    }

    public function setRoleWanted(string $roleWanted): self
    {
        $this->roleWanted = $roleWanted;

        return $this;
    }
}

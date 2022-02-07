<?php
namespace App\Entity\Traits;

use Gedmo\Mapping\Annotation as Gedmo;

trait TimestampableTrait{
    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function getCreatedAt(): ?\DateTimeInterface 
    {
        return $this->createdAt;
    }

    /*public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime();
        return $this;
    }*/

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /*public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime();
        return $this;
    }*/
}
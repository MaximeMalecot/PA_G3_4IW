<?php
namespace App\Entity\Traits;

use Gedmo\Mapping\Annotation as Gedmo;

trait SortableTrait{
    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int
     * @return self
     */
    public function setPosition(int $position): self 
    {
        $this->position = $position;
        return $this;
    }


}
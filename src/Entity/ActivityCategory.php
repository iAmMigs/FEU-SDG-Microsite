<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a dynamic category (e.g., News, Seminar, Workshop)
 * for the Activity/Event entity dropdowns.
 */
#[ORM\Entity]
class ActivityCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    public function getId(): ?int 
    { 
        return $this->id; 
    }

    public function getName(): ?string 
    { 
        return $this->name; 
    }

    public function setName(string $name): static 
    { 
        $this->name = $name; 
        return $this; 
    }

    public function __toString(): string 
    { 
        return (string) $this->name; 
    }
}
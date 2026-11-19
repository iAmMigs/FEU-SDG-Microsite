<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ProjectType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Thesis>
     */
    #[ORM\OneToMany(targetEntity: Thesis::class, mappedBy: 'type')]
    private Collection $theses;

    public function __construct()
    {
        $this->theses = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Thesis>
     */
    public function getTheses(): Collection
    {
        return $this->theses;
    }

    public function __toString(): string 
    { 
        return (string) $this->name; 
    }
}
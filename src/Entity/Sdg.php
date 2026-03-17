<?php

namespace App\Entity;

use App\Repository\SdgRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SdgRepository::class)]
class Sdg
{
    #[ORM\Id]
    #[ORM\Column]
    private ?int $id = null; // This IS the SDG number (e.g., 1 to 17)

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Thesis>
     */
    #[ORM\ManyToMany(targetEntity: Thesis::class, mappedBy: 'sdgs')]
    private Collection $theses;

    public function __construct()
    {
        $this->theses = new ArrayCollection();
    }

    public function __toString(): string
    {
        return 'SDG ' . $this->id . ' - ' . $this->name;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(int $id): static { $this->id = $id; return $this; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    /** @return Collection<int, Thesis> */
    public function getTheses(): Collection { return $this->theses; }

    public function addThesis(Thesis $thesis): static
    {
        if (!$this->theses->contains($thesis)) {
            $this->theses->add($thesis);
            $thesis->addSdg($this);
        }
        return $this;
    }

    public function removeThesis(Thesis $thesis): static
    {
        if ($this->theses->removeElement($thesis)) {
            $thesis->removeSdg($this);
        }
        return $this;
    }
}
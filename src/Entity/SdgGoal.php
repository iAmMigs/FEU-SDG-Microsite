<?php

namespace App\Entity;

use App\Repository\SdgGoalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SdgGoalRepository::class)]
class SdgGoal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $goalNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Thesis>
     */
    #[ORM\ManyToMany(targetEntity: Thesis::class, mappedBy: 'sdgGoals')]
    private Collection $theses;

    public function __construct()
    {
        $this->theses = new ArrayCollection();
    }

    public function __toString(): string
    {
        return 'SDG ' . $this->goalNumber . ' - ' . $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGoalNumber(): ?int
    {
        return $this->goalNumber;
    }

    public function setGoalNumber(int $goalNumber): static
    {
        $this->goalNumber = $goalNumber;
        return $this;
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

    public function addThesis(Thesis $thesis): static
    {
        if (!$this->theses->contains($thesis)) {
            $this->theses->add($thesis);
            $thesis->addSdgGoal($this);
        }
        return $this;
    }

    public function removeThesis(Thesis $thesis): static
    {
        if ($this->theses->removeElement($thesis)) {
            $thesis->removeSdgGoal($this);
        }
        return $this;
    }
}
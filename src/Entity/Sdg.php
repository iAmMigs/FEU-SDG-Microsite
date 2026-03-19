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

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\ManyToMany(targetEntity: Activity::class, mappedBy: 'sdgs')]
    private Collection $activities;

    public function __construct()
    {
        $this->theses = new ArrayCollection();
        $this->activities = new ArrayCollection();
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

    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): static
    {
        if (!$this->activities->contains($activity)) {
            $this->activities->add($activity);
            $activity->addSdg($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        if ($this->activities->removeElement($activity)) {
            $activity->removeSdg($this);
        }

        return $this;
    }
}
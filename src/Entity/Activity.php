<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a news article, event, or community activity.
 */
#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(targetEntity: ActivityCategory::class)]
    private ?ActivityCategory $category = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $eventDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Sdg>
     */
    #[ORM\ManyToMany(targetEntity: Sdg::class, inversedBy: 'activities')]
    private Collection $sdgs;

    #[ORM\Column]
    private ?bool $isActive = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $publishAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->sdgs = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getCategory(): ?ActivityCategory { return $this->category; }
    public function setCategory(?ActivityCategory $category): static { $this->category = $category; return $this; }

    public function setContent(?string $content): static 
    { 
        $this->content = $content ?? ''; 
        return $this; 
    }

    public function getContent(): string 
    { 
        return $this->content ?? ''; 
    }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static { $this->image = $image; return $this; }

    public function getEventDate(): ?\DateTimeInterface { return $this->eventDate; }
    public function setEventDate(\DateTimeInterface $eventDate): static { $this->eventDate = $eventDate; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    /**
     * @return Collection<int, Sdg>
     */
    public function getSdgs(): Collection
    {
        return $this->sdgs;
    }

    public function addSdg(Sdg $sdg): static
    {
        if (!$this->sdgs->contains($sdg)) {
            $this->sdgs->add($sdg);
        }

        return $this;
    }

    public function removeSdg(Sdg $sdg): static
    {
        $this->sdgs->removeElement($sdg);

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getPublishAt(): ?\DateTime
    {
        return $this->publishAt;
    }

    public function setPublishAt(?\DateTime $publishAt): static
    {
        $this->publishAt = $publishAt;

        return $this;
    }
}
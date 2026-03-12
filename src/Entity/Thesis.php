<?php

namespace App\Entity;

use App\Repository\ThesisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThesisRepository::class)]
class Thesis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $authors = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $views = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $documentFile = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, SdgGoal>
     */
    #[ORM\ManyToMany(targetEntity: SdgGoal::class, inversedBy: 'theses')]
    private Collection $sdgGoals;

    public function __construct()
    {
        $this->sdgGoals = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    // --- GETTERS & SETTERS ---

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getAuthors(): ?string { return $this->authors; }
    public function setAuthors(string $authors): static { $this->authors = $authors; return $this; }

    public function getViews(): ?int { return $this->views; }
    public function setViews(int $views): static { $this->views = $views; return $this; }
    public function incrementViews(): static { $this->views++; return $this; }

    public function getCoverImage(): ?string { return $this->coverImage; }
    public function setCoverImage(?string $coverImage): static { $this->coverImage = $coverImage; return $this; }

    public function getDocumentFile(): ?string { return $this->documentFile; }
    public function setDocumentFile(?string $documentFile): static { $this->documentFile = $documentFile; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    /** @return Collection<int, SdgGoal> */
    public function getSdgGoals(): Collection { return $this->sdgGoals; }

    public function addSdgGoal(SdgGoal $sdgGoal): static
    {
        if (!$this->sdgGoals->contains($sdgGoal)) {
            $this->sdgGoals->add($sdgGoal);
        }
        return $this;
    }

    public function removeSdgGoal(SdgGoal $sdgGoal): static
    {
        $this->sdgGoals->removeElement($sdgGoal);
        return $this;
    }
}
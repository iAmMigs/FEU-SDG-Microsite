<?php

namespace App\Entity;

use App\Repository\ThesisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThesisRepository::class)]
#[ORM\Index(name: 'idx_thesis_search', columns: ['title', 'authors'])]
#[ORM\Index(name: 'idx_thesis_trending', columns: ['views'])]
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $college = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $views = 0;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $regionViews = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $documentFile = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $publicationLink = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $isActive = false;

    /**
     * @var Collection<int, Sdg>
     */
    #[ORM\ManyToMany(targetEntity: Sdg::class, inversedBy: 'theses')]
    private Collection $sdgs;

    public function __construct()
    {
        $this->sdgs = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->isActive = false;
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getAuthors(): ?string { return $this->authors; }
    public function setAuthors(string $authors): static { $this->authors = $authors; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): static { $this->type = $type; return $this; }

    public function getCollege(): ?string { return $this->college; }
    public function setCollege(?string $college): static { $this->college = $college; return $this; }

    public function getViews(): ?int { return $this->views; }
    public function setViews(int $views): static { $this->views = $views; return $this; }

    public function getRegionViews(): ?array
    {
        return $this->regionViews ?? [];
    }

    public function setRegionViews(?array $regionViews): static
    {
        $this->regionViews = $regionViews;
        $this->calculateTotalViews(); 

        return $this;
    }

    public function getCoverImage(): ?string { return $this->coverImage; }
    public function setCoverImage(?string $coverImage): static { $this->coverImage = $coverImage; return $this; }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getDocumentFile(): ?string { return $this->documentFile; }
    public function setDocumentFile(?string $documentFile): static { $this->documentFile = $documentFile; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getPublicationLink(): ?string { return $this->publicationLink; }
    public function setPublicationLink(?string $publicationLink): static { $this->publicationLink = $publicationLink; return $this; }

    /** @return Collection<int, Sdg> */
    public function getSdgs(): Collection { return $this->sdgs; }

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

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function incrementViews(string $countryCode = 'Unknown'): static
    {
        $regions = $this->getRegionViews();
        
        if (isset($regions[$countryCode])) {
            $regions[$countryCode]++;
        } else {
            $regions[$countryCode] = 1;
        }
        
        $this->regionViews = $regions;
        $this->calculateTotalViews();

        return $this;
    }

    private function calculateTotalViews(): void
    {
        if (empty($this->regionViews)) {
            $this->views = 0;
            return;
        }

        $this->views = array_sum($this->regionViews);
    }
}
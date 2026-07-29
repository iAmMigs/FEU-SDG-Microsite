<?php

namespace App\Entity;

use App\Repository\LeadingVoiceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a featured researcher, faculty member, or public figure.
 */
#[ORM\Entity(repositoryClass: LeadingVoiceRepository::class)]
class LeadingVoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 22, maxMessage: 'The name cannot be longer than {{ limit }} characters.')]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 18, maxMessage: 'The professional title cannot be longer than {{ limit }} characters.')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Length(max: 100, maxMessage: 'The description cannot be longer than {{ limit }} characters.')]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static { $this->image = $image; return $this; }
}
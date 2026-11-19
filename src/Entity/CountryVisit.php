<?php

namespace App\Entity;

use App\Repository\CountryVisitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryVisitRepository::class)]
#[ORM\Table(name: 'country_visit')]
class CountryVisit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $countryName = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $countryCode = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $visitCount = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function setCountryName(string $countryName): static
    {
        $this->countryName = $countryName;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getVisitCount(): int
    {
        return $this->visitCount;
    }

    public function setVisitCount(int $visitCount): static
    {
        $this->visitCount = $visitCount;

        return $this;
    }

    public function incrementVisitCount(): static
    {
        $this->visitCount++;

        return $this;
    }
}

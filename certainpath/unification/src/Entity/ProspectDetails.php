<?php

namespace App\Entity;

use App\Repository\ProspectDetailsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProspectDetailsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ProspectDetails
{
    use Traits\TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'prospectDetails')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Prospect $prospect = null;

    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $infoBase = null;

    #[ORM\Column(nullable: true)]
    private ?int $yearBuilt = null;

    #[ORM\Column(nullable: true)]
    private ?int $estimatedIncome = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProspect(): ?Prospect
    {
        return $this->prospect;
    }

    public function setProspect(Prospect $prospect): static
    {
        $this->prospect = $prospect;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getInfoBase(): ?string
    {
        return $this->infoBase;
    }

    public function setInfoBase(?string $infoBase): static
    {
        $this->infoBase = $infoBase;

        return $this;
    }

    public function getYearBuilt(): ?int
    {
        return $this->yearBuilt;
    }

    public function setYearBuilt(?int $yearBuilt): static
    {
        $this->yearBuilt = $yearBuilt;

        return $this;
    }

    public function getEstimatedIncome(): ?int
    {
        return $this->estimatedIncome;
    }

    public function setEstimatedIncome(?int $estimatedIncome): static
    {
        $this->estimatedIncome = $estimatedIncome;

        return $this;
    }
}

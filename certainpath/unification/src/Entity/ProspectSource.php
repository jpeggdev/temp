<?php

namespace App\Entity;

use App\Repository\ProspectSourceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProspectSourceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ProspectSource
{
    use Traits\TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $license_start_date = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $license_end_date = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $refreshed_at = null;

    #[ORM\ManyToOne(inversedBy: 'prospectSources')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Prospect $prospect = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $previousJson = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $currentJson = null;

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

    public function getLicenseStartDate(): ?\DateTimeInterface
    {
        return $this->license_start_date;
    }

    public function setLicenseStartDate(?\DateTimeInterface $license_start_date): static
    {
        $this->license_start_date = $license_start_date;

        return $this;
    }

    public function getLicenseEndDate(): ?\DateTimeInterface
    {
        return $this->license_end_date;
    }

    public function setLicenseEndDate(\DateTimeInterface $license_end_date): static
    {
        $this->license_end_date = $license_end_date;

        return $this;
    }

    public function getRefreshedAt(): ?\DateTimeImmutable
    {
        return $this->refreshed_at;
    }

    public function setRefreshedAt(?\DateTimeImmutable $refreshed_at): static
    {
        $this->refreshed_at = $refreshed_at;

        return $this;
    }

    public function getProspect(): ?Prospect
    {
        return $this->prospect;
    }

    public function setProspect(?Prospect $prospect): static
    {
        $this->prospect = $prospect;

        return $this;
    }

    public function getPreviousJson(): ?string
    {
        return $this->previousJson;
    }

    public function setPreviousJson(?string $previousJson): static
    {
        $this->previousJson = $previousJson;

        return $this;
    }

    public function getCurrentJson(): ?string
    {
        return $this->currentJson;
    }

    public function setCurrentJson(string $json): static
    {
        $this->setPreviousJson(
            $this->getCurrentJson()
        );

        $this->currentJson = $json;

        return $this;
    }
}

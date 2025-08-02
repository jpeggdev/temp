<?php

namespace App\Entity;

use App\Repository\FieldServiceSoftwareRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FieldServiceSoftwareRepository::class)]
class FieldServiceSoftware
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\OneToMany(targetEntity: Company::class, mappedBy: 'fieldServiceSoftware')]
    private Collection $companies;

    public function __construct()
    {
        $this->companies = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): static
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
            $company->setFieldServiceSoftware($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        if ($this->companies->removeElement($company)) {
            // set the owning side to null (unless already changed)
            if ($company->getFieldServiceSoftware() === $this) {
                $company->setFieldServiceSoftware(null);
            }
        }

        return $this;
    }

    // region Domain-Specific Methods
    public static function serviceTitan(): self
    {
        return (new self())->setName('ServiceTitan');
    }

    public static function fieldEdge(): self
    {
        return (new self())->setName('FieldEdge');
    }

    public static function successWare(): self
    {
        return (new self())->setName('SuccessWare');
    }

    public static function other(): self
    {
        return (new self())->setName('Other');
    }

    public static function fromName(?string $softwareName): self
    {
        return (new self())->setName($softwareName);
    }

    public function is(FieldServiceSoftware $softwareToCompare): bool
    {
        return $this->getName() === $softwareToCompare->getName();
    }

    public function updateFromReference(FieldServiceSoftware $software): void
    {
        $this->setName($software->getName());
    }
    // endregion
}

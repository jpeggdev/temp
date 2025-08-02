<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ORM\Table(name: 'application')]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, ApplicationAccess>
     */
    #[ORM\OneToMany(targetEntity: ApplicationAccess::class, mappedBy: 'application')]
    private Collection $applicationAccesses;

    #[ORM\Column(length: 255)]
    private ?string $internalName = null;

    public function __construct()
    {
        $this->applicationAccesses = new ArrayCollection();
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
     * @return Collection<int, ApplicationAccess>
     */
    public function getApplicationAccesses(): Collection
    {
        return $this->applicationAccesses;
    }

    public function addApplicationAccess(ApplicationAccess $applicationAccess): static
    {
        if (!$this->applicationAccesses->contains($applicationAccess)) {
            $this->applicationAccesses->add($applicationAccess);
            $applicationAccess->setApplication($this);
        }

        return $this;
    }

    public function removeApplicationAccess(ApplicationAccess $applicationAccess): static
    {
        if ($this->applicationAccesses->removeElement($applicationAccess)) {
            // set the owning side to null (unless already changed)
            if ($applicationAccess->getApplication() === $this) {
                $applicationAccess->setApplication(null);
            }
        }

        return $this;
    }

    public function getInternalName(): ?string
    {
        return $this->internalName;
    }

    public function setInternalName(string $internalName): static
    {
        $this->internalName = $internalName;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\EmployeeRoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeeRoleRepository::class)]
#[ORM\UniqueConstraint(fields: ['name'])]
class EmployeeRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, ResourceEmployeeRoleMapping>
     */
    #[ORM\OneToMany(targetEntity: ResourceEmployeeRoleMapping::class, mappedBy: 'employeeRole')]
    private Collection $resourceEmployeeRoleMappings;

    /**
     * @var Collection<int, EventEmployeeRoleMapping>
     */
    #[ORM\OneToMany(targetEntity: EventEmployeeRoleMapping::class, mappedBy: 'employeeRole')]
    private Collection $eventEmployeeRoleMappings;

    public function __construct()
    {
        $this->resourceEmployeeRoleMappings = new ArrayCollection();
        $this->eventEmployeeRoleMappings = new ArrayCollection();
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
     * @return Collection<int, ResourceEmployeeRoleMapping>
     */
    public function getResourceEmployeeRoleMappings(): Collection
    {
        return $this->resourceEmployeeRoleMappings;
    }

    public function addResourceEmployeeRoleMapping(ResourceEmployeeRoleMapping $resourceEmployeeRoleMapping): static
    {
        if (!$this->resourceEmployeeRoleMappings->contains($resourceEmployeeRoleMapping)) {
            $this->resourceEmployeeRoleMappings->add($resourceEmployeeRoleMapping);
            $resourceEmployeeRoleMapping->setEmployeeRole($this);
        }

        return $this;
    }

    public function removeResourceEmployeeRoleMapping(ResourceEmployeeRoleMapping $resourceEmployeeRoleMapping): static
    {
        if ($this->resourceEmployeeRoleMappings->removeElement($resourceEmployeeRoleMapping)) {
            // set the owning side to null (unless already changed)
            if ($resourceEmployeeRoleMapping->getEmployeeRole() === $this) {
                $resourceEmployeeRoleMapping->setEmployeeRole(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EventEmployeeRoleMapping>
     */
    public function getEventEmployeeRoleMappings(): Collection
    {
        return $this->eventEmployeeRoleMappings;
    }

    public function addEventEmployeeRoleMapping(EventEmployeeRoleMapping $eventEmployeeRoleMapping): static
    {
        if (!$this->eventEmployeeRoleMappings->contains($eventEmployeeRoleMapping)) {
            $this->eventEmployeeRoleMappings->add($eventEmployeeRoleMapping);
            $eventEmployeeRoleMapping->setEmployeeRole($this);
        }

        return $this;
    }

    public function removeEventEmployeeRoleMapping(EventEmployeeRoleMapping $eventEmployeeRoleMapping): static
    {
        if ($this->eventEmployeeRoleMappings->removeElement($eventEmployeeRoleMapping)) {
            // set the owning side to null (unless already changed)
            if ($eventEmployeeRoleMapping->getEmployeeRole() === $this) {
                $eventEmployeeRoleMapping->setEmployeeRole(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\ResourceEmployeeRoleMappingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceEmployeeRoleMappingRepository::class)]
class ResourceEmployeeRoleMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'resourceEmployeeRoleMappings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Resource $resource = null;

    #[ORM\ManyToOne(inversedBy: 'resourceEmployeeRoleMappings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?EmployeeRole $employeeRole = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResource(): ?Resource
    {
        return $this->resource;
    }

    public function setResource(?Resource $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    public function getEmployeeRole(): ?EmployeeRole
    {
        return $this->employeeRole;
    }

    public function setEmployeeRole(?EmployeeRole $employeeRole): static
    {
        $this->employeeRole = $employeeRole;

        return $this;
    }
}

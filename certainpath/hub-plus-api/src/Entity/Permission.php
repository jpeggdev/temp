<?php

namespace App\Entity;

use App\Entity\Trait\IsCertainPathTrait;
use App\Repository\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table(name: 'permission')]
class Permission
{
    use TimestampableEntity;
    use IsCertainPathTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $internalName = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    /**
     * @var Collection<int, BusinessRolePermission>
     */
    #[ORM\OneToMany(targetEntity: BusinessRolePermission::class, mappedBy: 'permission')]
    private Collection $rolePermissions;

    #[ORM\ManyToOne(inversedBy: 'permissions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PermissionGroup $permissionGroup = null;

    /**
     * @var Collection<int, EmployeePermission>
     */
    #[ORM\OneToMany(targetEntity: EmployeePermission::class, mappedBy: 'permission')]
    private Collection $employeePermissions;

    public function __construct()
    {
        $this->rolePermissions = new ArrayCollection();
        $this->employeePermissions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, BusinessRolePermission>
     */
    public function getRolePermissions(): Collection
    {
        return $this->rolePermissions;
    }

    public function addRolePermission(BusinessRolePermission $rolePermission): static
    {
        if (!$this->rolePermissions->contains($rolePermission)) {
            $this->rolePermissions->add($rolePermission);
            $rolePermission->setPermission($this);
        }

        return $this;
    }

    public function removeRolePermission(BusinessRolePermission $rolePermission): static
    {
        if ($this->rolePermissions->removeElement($rolePermission)) {
            if ($rolePermission->getPermission() === $this) {
                $rolePermission->setPermission(null);
            }
        }

        return $this;
    }

    public function getPermissionGroup(): ?PermissionGroup
    {
        return $this->permissionGroup;
    }

    public function setPermissionGroup(?PermissionGroup $permissionGroup): static
    {
        $this->permissionGroup = $permissionGroup;

        return $this;
    }

    /**
     * @return Collection<int, EmployeePermission>
     */
    public function getEmployeePermissions(): Collection
    {
        return $this->employeePermissions;
    }

    public function addEmployeePermission(EmployeePermission $employeePermission): static
    {
        if (!$this->employeePermissions->contains($employeePermission)) {
            $this->employeePermissions->add($employeePermission);
            $employeePermission->setPermission($this);
        }

        return $this;
    }

    public function removeEmployeePermission(EmployeePermission $employeePermission): static
    {
        if ($this->employeePermissions->removeElement($employeePermission)) {
            // set the owning side to null (unless already changed)
            if ($employeePermission->getPermission() === $this) {
                $employeePermission->setPermission(null);
            }
        }

        return $this;
    }
}

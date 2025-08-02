<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\IsCertainPathTrait;
use App\Repository\BusinessRoleRepository;
use App\ValueObject\Roster\RosterRole;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: BusinessRoleRepository::class)]
#[ORM\Table(name: 'business_role')]
class BusinessRole
{
    use TimestampableEntity;
    use IsCertainPathTrait;

    public const string ROLE_OWNER_GM = 'ROLE_OWNER_GM';
    public const string ROLE_MANAGER = 'ROLE_MANAGER';
    public const string ROLE_HR_RECRUITING = 'ROLE_HR_RECRUITING';
    public const string ROLE_FINANCE_BACK_OFFICE = 'ROLE_FINANCE_BACK_OFFICE';
    public const string ROLE_TECHNICIAN = 'ROLE_TECHNICIAN';
    public const string ROLE_CALL_CENTER = 'ROLE_CALL_CENTER';
    public const string ROLE_SALES = 'ROLE_SALES';
    public const string ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const string ROLE_MARKETING = 'ROLE_MARKETING';
    public const string ROLE_COACH = 'ROLE_COACH';
    public const string ROLE_COACH_DESCRIPTION = 'CertainPath Coach partnering with Clients on their Success Journey.';
    public const string ROLE_COACH_LABEL = 'Coach';
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $internalName = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, BusinessRolePermission>
     */
    #[ORM\OneToMany(targetEntity: BusinessRolePermission::class, mappedBy: 'role')]
    private Collection $rolePermissions;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private ?int $sortOrder = 0;

    public function __construct()
    {
        $this->rolePermissions = new ArrayCollection();
    }

    public static function ownerGm(): self
    {
        $role = new self();
        $role->setInternalName(
            self::ROLE_OWNER_GM
        );
        $role->setLabel('Owner/General Manager');
        $role->setDescription('Manages company settings and high-level functions.');
        $role->setCertainPath(false);

        return $role;
    }

    public static function manager(): self
    {
        $role = new self();
        $role->setInternalName(
            self::ROLE_MANAGER
        );
        $role->setLabel('Manager');
        $role->setDescription('Oversees team members and daily operations.');
        $role->setCertainPath(false);

        return $role;
    }

    public static function HRRecruiting(): self
    {
        $role = new self();
        $role->setInternalName(
            self::ROLE_HR_RECRUITING
        );
        $role->setLabel('HR Recruiting');
        $role->setDescription('Handles recruitment and HR tasks.');
        $role->setCertainPath(false);

        return $role;
    }

    public static function financeBackOffice(): self
    {
        $role = new self();
        $role->setInternalName(
            self::ROLE_FINANCE_BACK_OFFICE
        );
        $role->setLabel('Finance/Back Office');
        $role->setDescription('Manages financial records and back-office operations.');
        $role->setCertainPath(false);

        return $role;
    }

    public static function technician(): self
    {
        $role = new self();
        $role->setInternalName(
            self::ROLE_TECHNICIAN
        );
        $role->setLabel('Technician');
        $role->setDescription('Handles technical tasks and field work.');
        $role->setCertainPath(false);

        return $role;
    }

    public static function callCenter(): self
    {
        $role = new self();
        $role->setInternalName(
            self::ROLE_CALL_CENTER
        );
        $role->setLabel('Call Center');
        $role->setDescription('Manages customer calls and support.');
        $role->setCertainPath(false);

        return $role;
    }

    public static function sales(): self
    {
        $role = new self();
        $role->setInternalName(
            self::ROLE_SALES
        );
        $role->setLabel('Sales');
        $role->setDescription('Handles sales operations and client relationships.');
        $role->setCertainPath(false);

        return $role;
    }

    public static function superAdmin(): self
    {
        $role = new self();
        $role->setInternalName(
            self::ROLE_SUPER_ADMIN
        );
        $role->setLabel('Super Admin');
        $role->setDescription('Has full access to all system functions.');
        $role->setCertainPath(true);

        return $role;
    }

    public static function marketing(): self
    {
        $role = new self();
        $role->setInternalName(
            self::ROLE_MARKETING
        );
        $role->setLabel('Marketing');
        $role->setDescription('Oversees marketing campaigns and strategies.');
        $role->setCertainPath(true);

        return $role;
    }

    public static function coach(): self
    {
        $role = new self();
        $role->setInternalName(
            self::ROLE_COACH
        );
        $role->setLabel(self::ROLE_COACH_LABEL);
        $role->setDescription(self::ROLE_COACH_DESCRIPTION);
        $role->setCertainPath(true);

        return $role;
    }

    public static function fromRosterRole(
        RosterRole $rosterRole,
    ): self {
        $internalName = $rosterRole->getInternalName();

        return match ($internalName) {
            self::ROLE_OWNER_GM => self::ownerGm(),
            self::ROLE_MANAGER => self::manager(),
            self::ROLE_MARKETING => self::marketing(),
            self::ROLE_HR_RECRUITING => self::HRRecruiting(),
            self::ROLE_FINANCE_BACK_OFFICE => self::financeBackOffice(),
            self::ROLE_TECHNICIAN => self::technician(),
            self::ROLE_CALL_CENTER => self::callCenter(),
            self::ROLE_SALES => self::sales(),
            default => self::ownerGm(),
        };
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

    public function setDescription(?string $description): static
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
            $rolePermission->setRole($this);
        }

        return $this;
    }

    public function removeRolePermission(BusinessRolePermission $rolePermission): static
    {
        if ($this->rolePermissions->removeElement($rolePermission)) {
            // set the owning side to null (unless already changed)
            if ($rolePermission->getRole() === $this) {
                $rolePermission->setRole(null);
            }
        }

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function updateFromReference(BusinessRole $role): void
    {
        $this->setDescription($role->getDescription());
        $this->setLabel($role->getLabel());
        $this->setInternalName($role->getInternalName());
        $this->setSortOrder($role->getSortOrder());
        $this->setUpdatedAt($role->getUpdatedAt());
        $this->setCreatedAt($role->getCreatedAt());
        $this->setCertainPath($role->isCertainPath());
    }

    public function is(BusinessRole $roleToCompare): bool
    {
        return $this->getInternalName() === $roleToCompare->getInternalName();
    }
}

<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Auth0\Symfony\Contracts\Models\UserInterface as Auth0UserInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'users')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, Auth0UserInterface
{
    use Traits\StatusEntity;
    use Traits\TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, unique: true)]
    private ?string $identifier = null;

    private array $attributes = [ ];

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $accessRoles = [ ];

    private array $roles = [ ];

    /**
     * @var Collection<int, Company>
     */
    #[ORM\ManyToMany(targetEntity: Company::class, inversedBy: 'users')]
    private Collection $companies;

    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const ROLE_SYSTEM_ADMIN = 'ROLE_SYSTEM_ADMIN';
    public const ROLE_ACCOUNT_ADMIN = 'ROLE_ACCOUNT_ADMIN';
    public const ROLE_USER = 'ROLE_USER';

    public function __construct()
    {
        $this->companies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->getIdentifier();
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getRoles(): array
    {
        return array_unique(
            array_merge(
                $this->roles,
                $this->getAccessRoles()
            )
        );
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->getIdentifier();
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function addAttribute(string $name, array $data): self
    {
        $this->attributes[$name] = $data;

        return $this;
    }

    public function addRole(string $role): static
    {
        $this->roles[] = $role;
        $this->roles = array_unique($this->roles);

        return $this;
    }

    public function getAccessRoles(): array
    {
        return $this->accessRoles;
    }

    public function setAccessRoles(array $accessRoles): static
    {
        $this->accessRoles = $accessRoles;

        return $this;
    }

    public function addAccessRole(string $accessRole): static
    {
        $this->accessRoles[] = $accessRole;
        $this->accessRoles = array_unique($this->accessRoles);

        return $this;
    }

    public function removeAccessRole(string $accessRole): static
    {
        $key = array_search($accessRole, $this->accessRoles, true);
        if ($key !== false) {
            unset($this->accessRoles[$key]);
        }

        return $this;
    }

    public function supportsClass($class): bool
    {
        return $class === __CLASS__;
    }

    public function getCompany(): ?Company
    {
        $company = $this->getCompanies()->filter(function(Company $a, $b) {
            if(!$b instanceof Company) {
                return $a;
            }

            return ($a->getCreatedAt() < $b->getCreatedAt()) ? $a : $b;
        })->first();

        return ($company) ?: null;
    }

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function getCompanyIdentifiers(): array
    {
        $companyIdentifiers = [ ];
        foreach($this->getCompanies() as $company) {
            $companyIdentifiers[] = $company->getIdentifier();
        }

        return $companyIdentifiers;
    }

    public function addCompany(Company $company): static
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
            $company->addUser($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        if ($this->companies->removeElement($company)) {
            $company->removeUser($this);
        }

        return $this;
    }

    public function removeAllCompanies(): static
    {
        foreach($this->getCompanies() as $company) {
            $this->removeCompany($company);
        }

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public function makeSuperAdmin(): static
    {
        return $this->addRole(self::ROLE_SUPER_ADMIN);
    }

    public function isSystemAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SYSTEM_ADMIN);
    }

    public function makeSystemAdmin(): static
    {
        return $this->addRole(self::ROLE_SYSTEM_ADMIN);
    }

    public function isCompanyAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ACCOUNT_ADMIN);
    }

    public function makeCompanyAdmin(): static
    {
        return $this->addRole(self::ROLE_ACCOUNT_ADMIN);
    }
}

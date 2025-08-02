<?php

declare(strict_types=1);

namespace App\Entity;

use App\Contract\Entity\AuditableInterface;
use App\Entity\Trait\UserTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(fields: ['email'])]
#[ORM\UniqueConstraint(fields: ['ssoId'])]
#[ORM\UniqueConstraint(fields: ['salesforceId'])]
#[ORM\UniqueConstraint(fields: ['uuid'])]
class User implements UserInterface, AuditableInterface
{
    use TimestampableEntity;
    use UserTrait;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * We dynamically populate this from the database.
     */
    private array $roles = [];

    /**
     * @var Collection<int, Employee>
     */
    #[ORM\OneToMany(targetEntity: Employee::class, mappedBy: 'user')]
    private Collection $employeeRecords;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $ssoId = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $salesforceId = null;

    /**
     * @var Collection<int, AuditLog>
     */
    #[ORM\OneToMany(targetEntity: AuditLog::class, mappedBy: 'author')]
    private Collection $auditLogs;

    public function __construct()
    {
        $this->employeeRecords = new ArrayCollection();
        $this->auditLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Employee>
     */
    public function getEmployeeRecords(): Collection
    {
        return $this->employeeRecords;
    }

    public function addEmployeeRecord(Employee $employeeRecord): static
    {
        if (!$this->employeeRecords->contains($employeeRecord)) {
            $this->employeeRecords->add($employeeRecord);
            $employeeRecord->setUser($this);
        }

        return $this;
    }

    public function removeEmployeeRecord(Employee $employeeRecord): static
    {
        if ($this->employeeRecords->removeElement($employeeRecord)) {
            // set the owning side to null (unless already changed)
            if ($employeeRecord->getUser() === $this) {
                $employeeRecord->setUser(null);
            }
        }

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getSsoId(): ?string
    {
        return $this->ssoId;
    }

    public function setSsoId(?string $ssoId): static
    {
        $this->ssoId = $ssoId;

        return $this;
    }

    public function getSalesforceId(): ?string
    {
        return $this->salesforceId;
    }

    public function setSalesforceId(?string $salesforceId): static
    {
        $this->salesforceId = $salesforceId;

        return $this;
    }

    /**
     * @return Collection<int, AuditLog>
     */
    public function getAuditLogs(): Collection
    {
        return $this->auditLogs;
    }

    public function addAuditLog(AuditLog $auditLog): static
    {
        if (!$this->auditLogs->contains($auditLog)) {
            $this->auditLogs->add($auditLog);
            $auditLog->setAuthor($this);
        }

        return $this;
    }

    public function removeAuditLog(AuditLog $auditLog): static
    {
        if ($this->auditLogs->removeElement($auditLog)) {
            if ($auditLog->getAuthor() === $this) {
                $auditLog->setAuthor(null);
            }
        }

        return $this;
    }
}

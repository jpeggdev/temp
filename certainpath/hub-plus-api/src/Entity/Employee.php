<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\UuidTrait;
use App\Repository\EmployeeRepository;
use App\ValueObject\Roster\RosterCoach;
use App\ValueObject\Roster\RosterEmployee;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ORM\Table(name: 'employee')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(columns: ['user_id', 'company_id'])]
#[ORM\UniqueConstraint(fields: ['workEmail'])]
#[ORM\UniqueConstraint(fields: ['uuid'])]
class Employee
{
    use TimestampableEntity;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'employeeRecords')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'employeeRecords')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Company $company = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?BusinessRole $role = null;

    /**
     * @var Collection<int, ApplicationAccess>
     */
    #[ORM\OneToMany(targetEntity: ApplicationAccess::class, mappedBy: 'employee')]
    private Collection $applicationAccesses;

    /**
     * @var Collection<int, EmployeePermission>
     */
    #[ORM\OneToMany(targetEntity: EmployeePermission::class, mappedBy: 'employee')]
    private Collection $employeePermissions;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $hireDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $terminationDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $workEmail = null;

    /**
     * @var Collection<int, EventFavorite>
     */
    #[ORM\OneToMany(targetEntity: EventFavorite::class, mappedBy: 'employee')]
    private Collection $eventFavorites;

    /**
     * @var Collection<int, EventCheckout>
     */
    #[ORM\OneToMany(targetEntity: EventCheckout::class, mappedBy: 'createdBy')]
    private Collection $eventCheckouts;

    /**
     * @var Collection<int, ResourceFavorite>
     */
    #[ORM\OneToMany(targetEntity: ResourceFavorite::class, mappedBy: 'employee')]
    private Collection $resourceFavorites;

    /**
     * @var Collection<int, EventEnrollment>
     */
    #[ORM\OneToMany(targetEntity: EventEnrollment::class, mappedBy: 'employee')]
    private Collection $eventEnrollments;

    /**
     * @var Collection<int, Payment>
     */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'createdBy')]
    private Collection $payments;

    /**
     * @var Collection<int, EventEnrollmentWaitlist>
     */
    #[ORM\OneToMany(targetEntity: EventEnrollmentWaitlist::class, mappedBy: 'employee')]
    private Collection $eventEnrollmentWaitlists;

    /**
     * @var Collection<int, PaymentProfile>
     */
    #[ORM\OneToMany(targetEntity: PaymentProfile::class, mappedBy: 'employee')]
    private Collection $paymentProfiles;

    /**
     * @var Collection<int, FilesystemNode>
     */
    #[ORM\OneToMany(targetEntity: FilesystemNode::class, mappedBy: 'createdBy')]
    private Collection $filesystemNodes;

    /**
     * @var Collection<int, FilesystemNode>
     */
    #[ORM\OneToMany(targetEntity: FilesystemNode::class, mappedBy: 'updatedBy')]
    private Collection $updatedFilesystemNodes;

    public function __construct()
    {
        $this->applicationAccesses = new ArrayCollection();
        $this->employeePermissions = new ArrayCollection();
        $this->eventFavorites = new ArrayCollection();
        $this->eventCheckouts = new ArrayCollection();
        $this->resourceFavorites = new ArrayCollection();
        $this->eventEnrollments = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->eventEnrollmentWaitlists = new ArrayCollection();
        $this->paymentProfiles = new ArrayCollection();
        $this->filesystemNodes = new ArrayCollection();
        $this->updatedFilesystemNodes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getRole(): ?BusinessRole
    {
        return $this->role;
    }

    public function setRole(?BusinessRole $role): static
    {
        $this->role = $role;

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
            $applicationAccess->setEmployee($this);
        }

        return $this;
    }

    public function removeApplicationAccess(ApplicationAccess $applicationAccess): static
    {
        if ($this->applicationAccesses->removeElement($applicationAccess)) {
            // set the owning side to null (unless already changed)
            if ($applicationAccess->getEmployee() === $this) {
                $applicationAccess->setEmployee(null);
            }
        }

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
            $employeePermission->setEmployee($this);
        }

        return $this;
    }

    public function removeEmployeePermission(EmployeePermission $employeePermission): static
    {
        if ($this->employeePermissions->removeElement($employeePermission)) {
            // set the owning side to null (unless already changed)
            if ($employeePermission->getEmployee() === $this) {
                $employeePermission->setEmployee(null);
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

    public function getHireDate(): ?\DateTimeInterface
    {
        return $this->hireDate;
    }

    public function setHireDate(?\DateTimeInterface $hireDate): static
    {
        $this->hireDate = $hireDate;

        return $this;
    }

    public function getTerminationDate(): ?\DateTimeInterface
    {
        return $this->terminationDate;
    }

    public function setTerminationDate(?\DateTimeInterface $terminationDate): static
    {
        $this->terminationDate = $terminationDate;

        return $this;
    }

    public function getWorkEmail(): ?string
    {
        return $this->workEmail;
    }

    public function setWorkEmail(?string $workEmail): static
    {
        $this->workEmail = $workEmail;

        return $this;
    }

    /**
     * @return Collection<int, EventFavorite>
     */
    public function getEventFavorites(): Collection
    {
        return $this->eventFavorites;
    }

    public function addEventFavorite(EventFavorite $eventFavorite): static
    {
        if (!$this->eventFavorites->contains($eventFavorite)) {
            $this->eventFavorites->add($eventFavorite);
            $eventFavorite->setEmployee($this);
        }

        return $this;
    }

    public function removeEventFavorite(EventFavorite $eventFavorite): static
    {
        if ($this->eventFavorites->removeElement($eventFavorite)) {
            // set the owning side to null (unless already changed)
            if ($eventFavorite->getEmployee() === $this) {
                $eventFavorite->setEmployee(null);
            }
        }

        return $this;
    }

    public function isRole(BusinessRole $roleToCompare): bool
    {
        if (!$this->getRole()) {
            return false;
        }

        return $this->getRole()->is($roleToCompare);
    }

    public function updateFromSalesforceCoachRecord(
        RosterCoach $salesForceCoach,
        BusinessRole $coachRole,
    ): void {
        $this->setFirstName($salesForceCoach->getFirstName());
        $this->setLastName($salesForceCoach->getLastName());
        // do not set work email on a Coach
        // because coach will be assigned to many companies
        // $this->setWorkEmail($salesForceCoach->getEmail());
        if (!$this->isRole(BusinessRole::coach())) {
            $this->setRole($coachRole);
        }
    }

    public function updateFromSalesforceEmployeeRecord(
        RosterEmployee $salesforceEmployee,
        ?BusinessRole $targetRole,
    ): void {
        $this->setFirstName($salesforceEmployee->getFirstName());
        $this->setLastName($salesforceEmployee->getLastName());
        // hold of on this because some folks have same work email
        // across multiple companies
        // $this->setWorkEmail($salesforceEmployee->getEmail());
        if ($targetRole) {
            $this->setRole($targetRole);
        }
    }

    /**
     * @return Collection<int, EventCheckout>
     */
    public function getEventCheckouts(): Collection
    {
        return $this->eventCheckouts;
    }

    public function addEventCheckout(EventCheckout $eventCheckout): static
    {
        if (!$this->eventCheckouts->contains($eventCheckout)) {
            $this->eventCheckouts->add($eventCheckout);
            $eventCheckout->setCreatedBy($this);
        }

        return $this;
    }

    public function removeEventCheckout(EventCheckout $eventCheckout): static
    {
        if ($this->eventCheckouts->removeElement($eventCheckout)) {
            // set the owning side to null (unless already changed)
            if ($eventCheckout->getCreatedBy() === $this) {
                $eventCheckout->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResourceFavorite>
     */
    public function getResourceFavorites(): Collection
    {
        return $this->resourceFavorites;
    }

    public function addResourceFavorite(ResourceFavorite $resourceFavorite): static
    {
        if (!$this->resourceFavorites->contains($resourceFavorite)) {
            $this->resourceFavorites->add($resourceFavorite);
            $resourceFavorite->setEmployee($this);
        }

        return $this;
    }

    public function removeResourceFavorite(ResourceFavorite $resourceFavorite): static
    {
        if ($this->resourceFavorites->removeElement($resourceFavorite)) {
            if ($resourceFavorite->getEmployee() === $this) {
                $resourceFavorite->setEmployee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EventEnrollment>
     */
    public function getEventEnrollments(): Collection
    {
        return $this->eventEnrollments;
    }

    public function addEventEnrollment(EventEnrollment $eventEnrollment): static
    {
        if (!$this->eventEnrollments->contains($eventEnrollment)) {
            $this->eventEnrollments->add($eventEnrollment);
            $eventEnrollment->setEmployee($this);
        }

        return $this;
    }

    public function removeEventEnrollment(EventEnrollment $eventEnrollment): static
    {
        if ($this->eventEnrollments->removeElement($eventEnrollment)) {
            // set the owning side to null (unless already changed)
            if ($eventEnrollment->getEmployee() === $this) {
                $eventEnrollment->setEmployee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setCreatedBy($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getCreatedBy() === $this) {
                $payment->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EventEnrollmentWaitlist>
     */
    public function getEventEnrollmentWaitlists(): Collection
    {
        return $this->eventEnrollmentWaitlists;
    }

    public function addEventEnrollmentWaitlist(EventEnrollmentWaitlist $eventEnrollmentWaitlist): static
    {
        if (!$this->eventEnrollmentWaitlists->contains($eventEnrollmentWaitlist)) {
            $this->eventEnrollmentWaitlists->add($eventEnrollmentWaitlist);
            $eventEnrollmentWaitlist->setEmployee($this);
        }

        return $this;
    }

    public function removeEventEnrollmentWaitlist(EventEnrollmentWaitlist $eventEnrollmentWaitlist): static
    {
        if ($this->eventEnrollmentWaitlists->removeElement($eventEnrollmentWaitlist)) {
            // set the owning side to null (unless already changed)
            if ($eventEnrollmentWaitlist->getEmployee() === $this) {
                $eventEnrollmentWaitlist->setEmployee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PaymentProfile>
     */
    public function getPaymentProfiles(): Collection
    {
        return $this->paymentProfiles;
    }

    public function addPaymentProfile(PaymentProfile $paymentProfile): static
    {
        if (!$this->paymentProfiles->contains($paymentProfile)) {
            $this->paymentProfiles->add($paymentProfile);
            $paymentProfile->setEmployee($this);
        }

        return $this;
    }

    public function removePaymentProfile(PaymentProfile $paymentProfile): static
    {
        if ($this->paymentProfiles->removeElement($paymentProfile)) {
            // set the owning side to null (unless already changed)
            if ($paymentProfile->getEmployee() === $this) {
                $paymentProfile->setEmployee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FilesystemNode>
     */
    public function getFilesystemNodes(): Collection
    {
        return $this->filesystemNodes;
    }

    public function addFilesystemNode(FilesystemNode $filesystemNode): static
    {
        if (!$this->filesystemNodes->contains($filesystemNode)) {
            $this->filesystemNodes->add($filesystemNode);
            $filesystemNode->setCreatedBy($this);
        }

        return $this;
    }

    public function removeFilesystemNode(FilesystemNode $filesystemNode): static
    {
        if ($this->filesystemNodes->removeElement($filesystemNode)) {
            // set the owning side to null (unless already changed)
            if ($filesystemNode->getCreatedBy() === $this) {
                $filesystemNode->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FilesystemNode>
     */
    public function getUpdatedFilesystemNodes(): Collection
    {
        return $this->updatedFilesystemNodes;
    }

    public function addUpdatedFilesystemNode(FilesystemNode $updatedFilesystemNode): static
    {
        if (!$this->updatedFilesystemNodes->contains($updatedFilesystemNode)) {
            $this->updatedFilesystemNodes->add($updatedFilesystemNode);
            $updatedFilesystemNode->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeUpdatedFilesystemNode(FilesystemNode $updatedFilesystemNode): static
    {
        if ($this->updatedFilesystemNodes->removeElement($updatedFilesystemNode)) {
            // set the owning side to null (unless already changed)
            if ($updatedFilesystemNode->getUpdatedBy() === $this) {
                $updatedFilesystemNode->setUpdatedBy(null);
            }
        }

        return $this;
    }
}

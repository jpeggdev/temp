<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Entity\Trait\UuidTrait;
use App\Enum\EventCheckoutSessionStatus;
use App\Repository\EventCheckoutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventCheckoutRepository::class)]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\UniqueConstraint(fields: ['confirmationNumber'])]
#[ORM\UniqueConstraint(
    name: 'unique_event_session_active',
    columns: ['created_by_id', 'event_session_id', 'company_id'],
    options: ['where' => "((status)::text = 'in_progress'::text)"]
)]
#[ORM\HasLifecycleCallbacks]
class EventCheckout
{
    use TimestampableDateTimeTZTrait;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $contactName = null;

    #[ORM\Column(length: 255)]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactPhone = null;
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $groupNotes = null;

    #[ORM\ManyToOne(inversedBy: 'eventCheckouts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Employee $createdBy = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $reservationExpiresAt = null;

    /**
     * @var Collection<int, EventCheckoutAttendee>
     */
    #[ORM\OneToMany(targetEntity: EventCheckoutAttendee::class, mappedBy: 'eventCheckout')]
    private Collection $eventCheckoutAttendees;

    /**
     * @var Collection<int, EventEnrollment>
     */
    #[ORM\OneToMany(targetEntity: EventEnrollment::class, mappedBy: 'eventCheckout')]
    private Collection $eventEnrollments;

    #[ORM\Column(enumType: EventCheckoutSessionStatus::class)]
    private ?EventCheckoutSessionStatus $status = EventCheckoutSessionStatus::IN_PROGRESS;

    #[ORM\ManyToOne(inversedBy: 'eventCheckouts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'SET NULL')]
    private ?EventSession $eventSession = null;

    #[ORM\ManyToOne(inversedBy: 'eventCheckouts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Company $company = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $confirmationNumber = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $finalizedAt = null;

    /**
     * @var Collection<int, EventEnrollmentWaitlist>
     */
    #[ORM\OneToMany(targetEntity: EventEnrollmentWaitlist::class, mappedBy: 'originalCheckout')]
    private Collection $eventEnrollmentWaitlists;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $authnetCustomerProfileId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $authnetPaymentProfileId = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $cardLast4 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $cardType = null;

    public function __construct()
    {
        $this->eventCheckoutAttendees = new ArrayCollection();
        $this->eventEnrollments = new ArrayCollection();
        $this->eventEnrollmentWaitlists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    public function setContactName(string $contactName): static
    {
        $this->contactName = $contactName;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?string $contactPhone): static
    {
        $this->contactPhone = $contactPhone;

        return $this;
    }

    public function getGroupNotes(): ?string
    {
        return $this->groupNotes;
    }

    public function setGroupNotes(?string $groupNotes): static
    {
        $this->groupNotes = $groupNotes;

        return $this;
    }

    public function getCreatedBy(): ?Employee
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Employee $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getReservationExpiresAt(): ?\DateTimeImmutable
    {
        return $this->reservationExpiresAt;
    }

    public function setReservationExpiresAt(?\DateTimeImmutable $reservationExpiresAt): static
    {
        $this->reservationExpiresAt = $reservationExpiresAt;

        return $this;
    }

    /**
     * @return Collection<int, EventCheckoutAttendee>
     */
    public function getEventCheckoutAttendees(): Collection
    {
        return $this->eventCheckoutAttendees;
    }

    public function addEventCheckoutAttendee(EventCheckoutAttendee $eventCheckoutAttendee): static
    {
        if (!$this->eventCheckoutAttendees->contains($eventCheckoutAttendee)) {
            $this->eventCheckoutAttendees->add($eventCheckoutAttendee);
            $eventCheckoutAttendee->setEventCheckout($this);
        }

        return $this;
    }

    public function removeEventCheckoutAttendee(EventCheckoutAttendee $eventCheckoutAttendee): static
    {
        if ($this->eventCheckoutAttendees->removeElement($eventCheckoutAttendee)) {
            // set the owning side to null (unless already changed)
            if ($eventCheckoutAttendee->getEventCheckout() === $this) {
                $eventCheckoutAttendee->setEventCheckout(null);
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
            $eventEnrollment->setEventCheckout($this);
        }

        return $this;
    }

    public function removeEventEnrollment(EventEnrollment $eventEnrollment): static
    {
        if ($this->eventEnrollments->removeElement($eventEnrollment)) {
            // set the owning side to null (unless already changed)
            if ($eventEnrollment->getEventCheckout() === $this) {
                $eventEnrollment->setEventCheckout(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?EventCheckoutSessionStatus
    {
        return $this->status;
    }

    public function setStatus(?EventCheckoutSessionStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getEventSession(): ?EventSession
    {
        return $this->eventSession;
    }

    public function setEventSession(?EventSession $eventSession): static
    {
        $this->eventSession = $eventSession;

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

    public function getConfirmationNumber(): ?string
    {
        return $this->confirmationNumber;
    }

    public function setConfirmationNumber(?string $confirmationNumber): static
    {
        $this->confirmationNumber = $confirmationNumber;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getFinalizedAt(): ?\DateTimeImmutable
    {
        return $this->finalizedAt;
    }

    public function setFinalizedAt(?\DateTimeImmutable $finalizedAt): static
    {
        $this->finalizedAt = $finalizedAt;

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
            $eventEnrollmentWaitlist->setOriginalCheckout($this);
        }

        return $this;
    }

    public function removeEventEnrollmentWaitlist(EventEnrollmentWaitlist $eventEnrollmentWaitlist): static
    {
        if ($this->eventEnrollmentWaitlists->removeElement($eventEnrollmentWaitlist)) {
            // set the owning side to null (unless already changed)
            if ($eventEnrollmentWaitlist->getOriginalCheckout() === $this) {
                $eventEnrollmentWaitlist->setOriginalCheckout(null);
            }
        }

        return $this;
    }

    public function hasOnlyWaitlistAttendees(): bool
    {
        if (0 === $this->eventCheckoutAttendees->count()) {
            return false;
        }
        foreach ($this->eventCheckoutAttendees as $attendee) {
            if (!$attendee->isWaitlist()) {
                return false;
            }
        }

        return true;
    }

    public function getAuthnetCustomerProfileId(): ?string
    {
        return $this->authnetCustomerProfileId;
    }

    public function setAuthnetCustomerProfileId(?string $authnetCustomerProfileId): void
    {
        $this->authnetCustomerProfileId = $authnetCustomerProfileId;
    }

    public function getAuthnetPaymentProfileId(): ?string
    {
        return $this->authnetPaymentProfileId;
    }

    public function setAuthnetPaymentProfileId(?string $authnetPaymentProfileId): void
    {
        $this->authnetPaymentProfileId = $authnetPaymentProfileId;
    }

    public function getCardLast4(): ?string
    {
        return $this->cardLast4;
    }

    public function setCardLast4(?string $cardLast4): void
    {
        $this->cardLast4 = $cardLast4;
    }

    public function getCardType(): ?string
    {
        return $this->cardType;
    }

    public function setCardType(?string $cardType): void
    {
        $this->cardType = $cardType;
    }
}

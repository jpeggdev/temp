<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Repository\EventEnrollmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventEnrollmentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(columns: ['employee_id', 'event_session_id'])]
#[ORM\UniqueConstraint(columns: ['email', 'event_session_id'])]
class EventEnrollment
{
    use TimestampableDateTimeTZTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeImmutable $enrolledAt = null;

    #[ORM\ManyToOne(inversedBy: 'eventEnrollments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'SET NULL')]
    private ?EventCheckout $eventCheckout = null;

    #[ORM\ManyToOne(inversedBy: 'eventEnrollments')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Employee $employee = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $specialRequests = null;

    #[ORM\ManyToOne(inversedBy: 'eventEnrollments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?EventSession $eventSession = null;

    /**
     * @var Collection<int, EmailCampaignEventEnrollment>
     */
    #[ORM\OneToMany(targetEntity: EmailCampaignEventEnrollment::class, mappedBy: 'eventEnrollment')]
    private Collection $emailCampaignEventEnrollments;

    public function __construct()
    {
        $this->emailCampaignEventEnrollments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnrolledAt(): ?\DateTimeImmutable
    {
        return $this->enrolledAt;
    }

    public function setEnrolledAt(\DateTimeImmutable $enrolledAt): static
    {
        $this->enrolledAt = $enrolledAt;

        return $this;
    }

    public function getEventCheckout(): ?EventCheckout
    {
        return $this->eventCheckout;
    }

    public function setEventCheckout(?EventCheckout $eventCheckout): static
    {
        $this->eventCheckout = $eventCheckout;

        return $this;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(?Employee $employee): static
    {
        $this->employee = $employee;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getSpecialRequests(): ?string
    {
        return $this->specialRequests;
    }

    public function setSpecialRequests(?string $specialRequests): static
    {
        $this->specialRequests = $specialRequests;

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

    /**
     * @return Collection<int, EmailCampaignEventEnrollment>
     */
    public function getEmailCampaignEventEnrollments(): Collection
    {
        return $this->emailCampaignEventEnrollments;
    }

    public function addEmailCampaignEventEnrollment(
        EmailCampaignEventEnrollment $ece,
    ): static {
        if (!$this->emailCampaignEventEnrollments->contains($ece)) {
            $this->emailCampaignEventEnrollments->add($ece);
            $ece->setEventEnrollment($this);
        }

        return $this;
    }

    public function removeEmailCampaignEventEnrollment(
        EmailCampaignEventEnrollment $ece,
    ): static {
        if ($this->emailCampaignEventEnrollments->removeElement($ece)) {
            if ($ece->getEventEnrollment() === $this) {
                $ece->setEventEnrollment(null);
            }
        }

        return $this;
    }
}

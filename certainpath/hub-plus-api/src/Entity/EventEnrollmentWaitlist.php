<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Repository\EventEnrollmentWaitlistRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventEnrollmentWaitlistRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EventEnrollmentWaitlist
{
    use TimestampableDateTimeTZTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'eventEnrollmentWaitlists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EventSession $eventSession = null;

    #[ORM\ManyToOne(inversedBy: 'eventEnrollmentWaitlists')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Employee $employee = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $specialRequests = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeImmutable $waitlistedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $waitlistPosition = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $promotedAt = null;

    #[ORM\ManyToOne(inversedBy: 'eventEnrollmentWaitlists')]
    private ?EventCheckout $originalCheckout = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $seatPrice = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getWaitlistedAt(): ?\DateTimeImmutable
    {
        return $this->waitlistedAt;
    }

    public function setWaitlistedAt(?\DateTimeImmutable $waitlistedAt): static
    {
        $this->waitlistedAt = $waitlistedAt;

        return $this;
    }

    public function getWaitlistPosition(): ?int
    {
        return $this->waitlistPosition;
    }

    public function setWaitlistPosition(?int $waitlistPosition): static
    {
        $this->waitlistPosition = $waitlistPosition;

        return $this;
    }

    public function getPromotedAt(): ?\DateTimeImmutable
    {
        return $this->promotedAt;
    }

    public function setPromotedAt(?\DateTimeImmutable $promotedAt): static
    {
        $this->promotedAt = $promotedAt;

        return $this;
    }

    public function getOriginalCheckout(): ?EventCheckout
    {
        return $this->originalCheckout;
    }

    public function setOriginalCheckout(?EventCheckout $originalCheckout): static
    {
        $this->originalCheckout = $originalCheckout;

        return $this;
    }

    public function getSeatPrice(): ?string
    {
        return $this->seatPrice;
    }

    public function setSeatPrice(?string $seatPrice): void
    {
        $this->seatPrice = $seatPrice;
    }
}

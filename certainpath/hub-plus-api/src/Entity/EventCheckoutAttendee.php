<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Repository\EventCheckoutAttendeeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventCheckoutAttendeeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(columns: ['employee_id', 'event_checkout_id'])]
#[ORM\UniqueConstraint(columns: ['email', 'event_checkout_id'])]
class EventCheckoutAttendee
{
    use TimestampableDateTimeTZTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'eventCheckoutAttendees')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?EventCheckout $eventCheckout = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Employee $employee = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $specialRequests = null;

    #[ORM\Column(nullable: false, options: ['default' => 'false'])]
    private ?bool $isSelected = false;

    #[ORM\Column(nullable: false, options: ['default' => 'false'])]
    private ?bool $isWaitlist = false;

    public function getId(): ?int
    {
        return $this->id;
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

    public function isSelected(): ?bool
    {
        return $this->isSelected;
    }

    public function setIsSelected(?bool $isSelected): static
    {
        $this->isSelected = $isSelected;

        return $this;
    }

    public function isWaitlist(): ?bool
    {
        return $this->isWaitlist;
    }

    public function setIsWaitlist(bool $isWaitlist): static
    {
        $this->isWaitlist = $isWaitlist;

        return $this;
    }
}

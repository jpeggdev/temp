<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\PaymentProfileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentProfileRepository::class)]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\UniqueConstraint(
    name: 'unique_payment_profile',
    fields: ['employee', 'authnetCustomerId', 'authnetPaymentProfileId']
)]
#[ORM\HasLifecycleCallbacks]
class PaymentProfile
{
    use TimestampableDateTimeTZTrait;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $authnetCustomerId = null;

    #[ORM\Column(length: 255)]
    private ?string $authnetPaymentProfileId = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $cardLast4 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $cardType = null;

    #[ORM\ManyToOne(inversedBy: 'paymentProfiles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Employee $employee = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthnetCustomerId(): ?string
    {
        return $this->authnetCustomerId;
    }

    public function setAuthnetCustomerId(string $authnetCustomerId): static
    {
        $this->authnetCustomerId = $authnetCustomerId;

        return $this;
    }

    public function getAuthnetPaymentProfileId(): ?string
    {
        return $this->authnetPaymentProfileId;
    }

    public function setAuthnetPaymentProfileId(string $authnetPaymentProfileId): static
    {
        $this->authnetPaymentProfileId = $authnetPaymentProfileId;

        return $this;
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

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(?Employee $employee): static
    {
        $this->employee = $employee;

        return $this;
    }
}

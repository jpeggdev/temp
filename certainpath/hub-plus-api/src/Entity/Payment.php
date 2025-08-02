<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\PaymentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\UniqueConstraint(fields: ['transactionId'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'payment')]
class Payment
{
    use TimestampableDateTimeTZTrait;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $transactionId = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $errorCode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customerProfileId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentProfileId = null;

    #[ORM\Column(nullable: true)]
    private ?array $responseData = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cardType = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Employee $createdBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cardLast4 = null;

    /**
     * @var Collection<int, PaymentInvoice>
     */
    #[ORM\OneToMany(targetEntity: PaymentInvoice::class, mappedBy: 'payment')]
    private Collection $paymentInvoices;

    public function __construct()
    {
        $this->paymentInvoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(string $transactionId): static
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function setErrorCode(?string $errorCode): static
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function getCustomerProfileId(): ?string
    {
        return $this->customerProfileId;
    }

    public function setCustomerProfileId(?string $customerProfileId): static
    {
        $this->customerProfileId = $customerProfileId;

        return $this;
    }

    public function getPaymentProfileId(): ?string
    {
        return $this->paymentProfileId;
    }

    public function setPaymentProfileId(?string $paymentProfileId): static
    {
        $this->paymentProfileId = $paymentProfileId;

        return $this;
    }

    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    public function setResponseData(?array $responseData): static
    {
        $this->responseData = $responseData;

        return $this;
    }

    public function getCardType(): ?string
    {
        return $this->cardType;
    }

    public function setCardType(?string $cardType): static
    {
        $this->cardType = $cardType;

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

    public function getCardLast4(): ?string
    {
        return $this->cardLast4;
    }

    public function setCardLast4(?string $cardLast4): static
    {
        $this->cardLast4 = $cardLast4;

        return $this;
    }

    /**
     * @return Collection<int, PaymentInvoice>
     */
    public function getPaymentInvoices(): Collection
    {
        return $this->paymentInvoices;
    }

    public function addPaymentInvoice(PaymentInvoice $paymentInvoice): static
    {
        if (!$this->paymentInvoices->contains($paymentInvoice)) {
            $this->paymentInvoices->add($paymentInvoice);
            $paymentInvoice->setPayment($this);
        }

        return $this;
    }

    public function removePaymentInvoice(PaymentInvoice $paymentInvoice): static
    {
        if ($this->paymentInvoices->removeElement($paymentInvoice)) {
            // set the owning side to null (unless already changed)
            if ($paymentInvoice->getPayment() === $this) {
                $paymentInvoice->setPayment(null);
            }
        }

        return $this;
    }
}

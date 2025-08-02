<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use App\ValueObjects\InvoiceObject;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: "invoice_company_external_uniq", columns: ["company_id", "external_id"])]
#[ORM\Index(name: 'invoice_external_id_idx', columns: ['external_id'])]
#[ORM\Index(
    name: 'invoice_company_customer_invoiceNumber_idx',
    columns: ['company_id', 'customer_id', 'invoice_number']
)]
class Invoice
{
    use Traits\ExternalIdEntity;
    use Traits\StatusEntity;
    use Traits\TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $identifier = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => 0.00])]
    private float $total = 0.00;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => 0.00])]
    private float $balance = 0.00;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => 0.00])]
    private float $subTotal = 0.00;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => 0.00])]
    private float $tax = 0.00;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $invoiceNumber = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $revenueType = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $invoicedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?Subscription $subscription = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?BusinessUnit $businessUnit = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Trade $trade = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $jobType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $zone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getSubTotal(): float
    {
        return $this->subTotal;
    }

    public function setSubTotal(float $subTotal): static
    {
        $this->subTotal = $subTotal;

        return $this;
    }

    public function getTax(): float
    {
        return $this->tax;
    }

    public function setTax(float $tax): static
    {
        $this->tax = $tax;

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

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): static
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getBusinessUnit(): ?BusinessUnit
    {
        return $this->businessUnit;
    }

    public function setBusinessUnit(?BusinessUnit $businessUnit): static
    {
        $this->businessUnit = $businessUnit;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

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

    public function getInvoicedAt(): ?DateTimeImmutable
    {
        return $this->invoicedAt;
    }

    public function setInvoicedAt(?DateTimeImmutable $invoicedAt): static
    {
        $this->invoicedAt = $invoicedAt;

        return $this;
    }

    public function fromValueObject(InvoiceObject $invoiceObject): static
    {
        $invoiceObject->populate();
        $this
            ->setActive($invoiceObject->isActive())
            ->setDeleted($invoiceObject->isDeleted())
            ->setTotal((float) $invoiceObject->total)
            ->setSubTotal((float) $invoiceObject->subTotal)
            ->setTax((float) $invoiceObject->tax)
            ->setBalance((float) $invoiceObject->balance)
            ->setDescription($invoiceObject->description)
            ->setInvoicedAt($invoiceObject->invoicedAt)
            ->setExternalId($invoiceObject->externalId)
            ->setJobType($invoiceObject->jobType)
            ->setZone($invoiceObject->zone)
            ->setSummary($invoiceObject->summary)
        ;

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): static
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getRevenueType(): ?string
    {
        return $this->revenueType;
    }

    public function setRevenueType(?string $revenueType): static
    {
        $this->revenueType = $revenueType;

        return $this;
    }

    public function setTrade(Trade $trade): static
    {
        $this->trade = $trade;

        return $this;
    }

    public function getTrade(): ?Trade
    {
        return $this->trade;
    }

    public function isNew(): bool
    {
        return $this->getId() === null;
    }

    public function getJobType(): ?string
    {
        return $this->jobType;
    }

    public function setJobType(?string $jobType): static
    {
        $this->jobType = $jobType;

        return $this;
    }

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(?string $zone): static
    {
        $this->zone = $zone;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }
}

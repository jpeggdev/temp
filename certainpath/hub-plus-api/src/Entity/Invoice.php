<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Entity\Trait\UuidTrait;
use App\Enum\InvoiceStatusType;
use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\UniqueConstraint(name: 'unique_invoice_number', fields: ['invoiceNumber'])]
#[ORM\HasLifecycleCallbacks]
class Invoice
{
    use TimestampableDateTimeTZTrait;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $invoiceNumber = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    private ?EventSession $eventSession = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeImmutable $invoiceDate = null;

    #[ORM\Column(enumType: InvoiceStatusType::class)]
    private ?InvoiceStatusType $status = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $totalAmount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    /**
     * @var Collection<int, InvoiceLineItem>
     */
    #[ORM\OneToMany(targetEntity: InvoiceLineItem::class, mappedBy: 'invoice')]
    private Collection $invoiceLineItems;

    /**
     * @var Collection<int, CreditMemo>
     */
    #[ORM\OneToMany(targetEntity: CreditMemo::class, mappedBy: 'invoice')]
    private Collection $creditMemos;

    /**
     * @var Collection<int, PaymentInvoice>
     */
    #[ORM\OneToMany(targetEntity: PaymentInvoice::class, mappedBy: 'invoice')]
    private Collection $paymentInvoices;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => 'false'])]
    private bool $canBeSynced = false;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $syncAttempts = 0;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $syncedAt = null;

    public function __construct()
    {
        $this->invoiceLineItems = new ArrayCollection();
        $this->creditMemos = new ArrayCollection();
        $this->paymentInvoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEventSession(): ?EventSession
    {
        return $this->eventSession;
    }

    public function setEventSession(?EventSession $eventSession): static
    {
        $this->eventSession = $eventSession;

        return $this;
    }

    public function getInvoiceDate(): ?\DateTimeImmutable
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(\DateTimeImmutable $invoiceDate): static
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }

    public function getStatus(): ?InvoiceStatusType
    {
        return $this->status;
    }

    public function setStatus(?InvoiceStatusType $status): void
    {
        $this->status = $status;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Collection<int, InvoiceLineItem>
     */
    public function getInvoiceLineItems(): Collection
    {
        return $this->invoiceLineItems;
    }

    public function addInvoiceLineItem(InvoiceLineItem $invoiceLineItem): static
    {
        if (!$this->invoiceLineItems->contains($invoiceLineItem)) {
            $this->invoiceLineItems->add($invoiceLineItem);
            $invoiceLineItem->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceLineItem(InvoiceLineItem $invoiceLineItem): static
    {
        if ($this->invoiceLineItems->removeElement($invoiceLineItem)) {
            // set the owning side to null (unless already changed)
            if ($invoiceLineItem->getInvoice() === $this) {
                $invoiceLineItem->setInvoice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CreditMemo>
     */
    public function getCreditMemos(): Collection
    {
        return $this->creditMemos;
    }

    public function addCreditMemo(CreditMemo $creditMemo): static
    {
        if (!$this->creditMemos->contains($creditMemo)) {
            $this->creditMemos->add($creditMemo);
            $creditMemo->setInvoice($this);
        }

        return $this;
    }

    public function removeCreditMemo(CreditMemo $creditMemo): static
    {
        if ($this->creditMemos->removeElement($creditMemo)) {
            // set the owning side to null (unless already changed)
            if ($creditMemo->getInvoice() === $this) {
                $creditMemo->setInvoice(null);
            }
        }

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): static
    {
        $this->invoiceNumber = $invoiceNumber;

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
            $paymentInvoice->setInvoice($this);
        }

        return $this;
    }

    public function removePaymentInvoice(PaymentInvoice $paymentInvoice): static
    {
        if ($this->paymentInvoices->removeElement($paymentInvoice)) {
            // set the owning side to null (unless already changed)
            if ($paymentInvoice->getInvoice() === $this) {
                $paymentInvoice->setInvoice(null);
            }
        }

        return $this;
    }

    public function isCanBeSynced(): ?bool
    {
        return $this->canBeSynced;
    }

    public function setCanBeSynced(bool $canBeSynced): static
    {
        $this->canBeSynced = $canBeSynced;

        return $this;
    }

    public function getSyncAttempts(): ?int
    {
        return $this->syncAttempts;
    }

    public function setSyncAttempts(int $syncAttempts): static
    {
        $this->syncAttempts = $syncAttempts;

        return $this;
    }

    public function incrementSyncAttempts(): static
    {
        ++$this->syncAttempts;

        return $this;
    }

    public function getSyncedAt(): ?\DateTimeImmutable
    {
        return $this->syncedAt;
    }

    public function setSyncedAt(?\DateTimeImmutable $syncedAt = null): static
    {
        if ($syncedAt instanceof \DateTimeImmutable) {
            $syncedAt->setTimezone(new \DateTimeZone('UTC'));
        }

        $this->syncedAt = $syncedAt;

        return $this;
    }

    public function getAccountingId(): ?string
    {
        if (!$this->company) {
            return null;
        }

        return $this->company->getIntacctId() ?? $this->company->getSalesforceId();
    }
}

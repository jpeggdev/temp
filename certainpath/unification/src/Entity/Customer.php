<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use App\ValueObjects\CustomerObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'customer_version_idx', columns: ['version'])]
class Customer
{
    use Traits\StatusEntity;
    use Traits\TimestampableEntity;

    public const INSTALLATION_THRESHOLD_FOR_INVOICE = 2500.0;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $isNewCustomer = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $isRepeatCustomer = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $hasInstallation = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $hasSubscription = false;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private ?int $countInvoices = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private ?int $legacyCountInvoices = 0;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $lastInvoicedAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $firstInvoicedAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $legacyFirstInvoicedAt = null;

    #[ORM\Column(type: Types::TEXT, options: ['default' => '0.00'])]
    private ?string $balanceTotal = '0.00';

    #[ORM\Column(type: Types::TEXT, options: ['default' => '0.00'])]
    private ?string $invoiceTotal = '0.00';

    #[ORM\Column(type: Types::TEXT, options: ['default' => '0.00'])]
    private ?string $lifetimeValue = '0.00';

    #[ORM\Column(type: Types::TEXT, options: ['default' => '0.00'])]
    private ?string $legacyLifetimeValue = '0.00';

    #[ORM\Column(type: Types::TEXT, options: ['default' => '0.00'])]
    private ?string $legacyFirstSaleAmount = '0.00';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $legacyLastInvoiceNumber = null;

    #[ORM\OneToOne(mappedBy: 'customer')]
    private ?Prospect $prospect = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $version = null;

    /**
     * @var Collection<int, Subscription>
     */
    #[ORM\OneToMany(targetEntity: Subscription::class, mappedBy: 'customer')]
    private Collection $subscriptions;

    /**
     * @var Collection<int, Invoice>
     */
    #[ORM\OneToMany(targetEntity: Invoice::class, mappedBy: 'customer')]
    private Collection $invoices;

    #[ORM\ManyToOne(inversedBy: 'customers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    /**
     * @var Collection<int, Address>
     */
    #[ORM\JoinTable(name: 'customer_address')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'address_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Address::class, inversedBy: 'customers')]
    private Collection $addresses;

    public function __construct()
    {
        //only the Active Customer Upload Process
        // should set a Customer to Active
        // intentionally defaulting to false
        $this->setActive(false);
        $this->addresses = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
        $this->invoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getAddressByExternalId(string $externalId): ?Address
    {
        foreach ($this->getAddresses() as $address) {
            if ($address->getExternalId() === $externalId) {
                return $address;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, Address>
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): static
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->addCustomer($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): static
    {
        $this->addresses->removeElement($address);
        $address->removeCustomer($this);

        return $this;
    }

    public function getProspect(): ?Prospect
    {
        return $this->prospect;
    }

    public function setProspect(?Prospect $prospect): static
    {
        $this->prospect = $prospect;
        if ($prospect instanceof Prospect) {
            $prospect->setCustomer($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): static
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setCustomer($this);
        }

        return $this;
    }

    public function hasSubscriptions(): bool
    {
        return (bool) $this->getSubscriptions()->count();
    }

    public function removeSubscription(Subscription $subscription): static
    {
        $this->subscriptions->removeElement($subscription);

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): static
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setCustomer($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        $this->invoices->removeElement($invoice);

        return $this;
    }

    public function hasInvoices(): bool
    {
        return (bool) $this->getInvoices()->count();
    }

    public function updateHasSubscription(): static
    {
        $activeSubscriptions = $this->getSubscriptions()->filter(function (Subscription $subscription) {
            return $subscription->isActive();
        });

        $this->setHasSubscription(!$activeSubscriptions->isEmpty());

        return $this;
    }

    public function updateIsNewCustomer(): static
    {
        $this->setNewCustomer(false);

        if (
            $this->getFirstInvoicedAt() &&
            $this->getFirstInvoicedAt() > date_create('today -30 days')
        ) {
            $this->setNewCustomer(true);
        }

        return $this;
    }

    public function updateIsRepeatCustomer(): static
    {
        $this->setRepeatCustomer(false);

        if ($this->getCountInvoices() > 1) {
            $this->setRepeatCustomer(true);
        }

        return $this;
    }

    public function updateInvoiceCount(): static
    {
        $count = $this->getInvoices()->count();
//            + $this->getLegacyCountInvoices();
        $this->setCountInvoices($count);

        return $this;
    }

    public function getInvoicesByInvoiceDate(): Collection
    {
        $invoices = $this->getInvoices()->toArray();
        usort($invoices, static function (Invoice $a, Invoice $b) {
            return $b->getInvoicedAt() <=> $a->getInvoicedAt();
        });

        return new ArrayCollection($invoices);
    }

    public function updateFirstInvoiceDate(): static
    {
        if (($invoices = $this->getInvoicesByInvoiceDate()) && $firstInvoice = $invoices->first()) {
            $this->setFirstInvoicedAt($firstInvoice->getInvoicedAt());
        }

        if (
            $this->getLegacyFirstInvoicedAt() &&
            $this->getLegacyFirstInvoicedAt() < $this->getFirstInvoicedAt()
        ) {
            $this->setFirstInvoicedAt($this->getLegacyFirstInvoicedAt());
        }

        return $this;
    }

    public function updateLastInvoiceDate(): static
    {
        if ($invoices = $this->getInvoicesByInvoiceDate()) {
            $lastInvoice = $invoices->last();
            if (
                $lastInvoice &&
                $lastInvoice->getInvoicedAt()
            ) {
                $this->setLastInvoicedAt($lastInvoice->getInvoicedAt());
            }
        }

        if (
            $this->getLegacyFirstInvoicedAt() &&
            $this->getLegacyFirstInvoicedAt() > $this->getLastInvoicedAt()
        ) {
            $this->setLastInvoicedAt($this->getLegacyFirstInvoicedAt());
        }

        return $this;
    }

    public function updateBalanceTotal(): static
    {
        $sum = 0;
        foreach ($this->getInvoices() as $invoice) {
            $sum += (float) $invoice->getBalance();
        }

        $this->setBalanceTotal(number_format($sum, 2, '.', ''));

        return $this;
    }

    public function updateInvoiceTotal(): static
    {
        $sum = 0;
        foreach ($this->getInvoices() as $invoice) {
            $sum += (float) $invoice->getTotal();
        }

        $this->setInvoiceTotal(number_format($sum, 2, '.', ''));

        return $this;
    }

    public function updateLifetimeValue(): static
    {
        $ltv =
            (float) $this->getInvoiceTotal() -
            (float) $this->getBalanceTotal()
//            +
//            (float) $this->getLegacyLifetimeValue()
        ;

        $this->setLifetimeValue(number_format($ltv, 2, '.', ''));

        return $this;
    }

    public function updateCustomerMetrics(): static
    {
        $this
            ->updateInvoiceTotal()
            ->updateBalanceTotal()
            ->updateFirstInvoiceDate()
            ->updateLastInvoiceDate()
            ->updateInvoiceCount()
            ->updateHasSubscription()
            ->updateHasInstallation()
        ;

        $this
            ->updateIsNewCustomer()
            ->updateIsRepeatCustomer()
            ->updateLifetimeValue()
        ;

        return $this;
    }

    public function isNewCustomer(): ?bool
    {
        return $this->isNewCustomer;
    }

    public function setNewCustomer(bool $isNewCustomer): static
    {
        $this->isNewCustomer = $isNewCustomer;

        return $this;
    }

    public function isRepeatCustomer(): ?bool
    {
        return $this->isRepeatCustomer;
    }

    public function setRepeatCustomer(bool $isRepeatCustomer): static
    {
        $this->isRepeatCustomer = $isRepeatCustomer;

        return $this;
    }

    public function getCountInvoices(): ?int
    {
        return $this->countInvoices;
    }

    public function setCountInvoices(int $countInvoices): static
    {
        $this->countInvoices = $countInvoices;

        return $this;
    }

    public function getFirstInvoicedAt(): ?DateTimeImmutable
    {
        return $this->firstInvoicedAt;
    }

    public function setFirstInvoicedAt(?DateTimeImmutable $firstInvoicedAt): static
    {
        $this->firstInvoicedAt = $firstInvoicedAt;

        return $this;
    }

    public function getLastInvoicedAt(): ?DateTimeImmutable
    {
        return $this->lastInvoicedAt;
    }

    public function setLastInvoicedAt(?DateTimeImmutable $lastInvoicedAt): static
    {
        $this->lastInvoicedAt = $lastInvoicedAt;

        return $this;
    }

    public function getBalanceTotal(): ?string
    {
        return $this->balanceTotal;
    }

    public function setBalanceTotal(string $balanceTotal): static
    {
        $this->balanceTotal = $balanceTotal;

        return $this;
    }

    public function getInvoiceTotal(): ?string
    {
        return $this->invoiceTotal;
    }

    public function setInvoiceTotal(string $invoiceTotal): static
    {
        $this->invoiceTotal = $invoiceTotal;

        return $this;
    }

    public function getLifetimeValue(): ?string
    {
        return $this->lifetimeValue;
    }

    public function setLifetimeValue(string $lifetimeValue): static
    {
        $this->lifetimeValue = $lifetimeValue;

        return $this;
    }

    public function hasInstallation(): ?bool
    {
        return $this->hasInstallation;
    }

    public function setHasInstallation(bool $hasInstallation): static
    {
        $this->hasInstallation = $hasInstallation;

        return $this;
    }

    public function hasSubscription(): ?bool
    {
        return $this->hasSubscription;
    }

    public function setHasSubscription(bool $hasSubscription): static
    {
        $this->hasSubscription = $hasSubscription;

        return $this;
    }

    public function getLegacyLifetimeValue(): ?string
    {
        return $this->legacyLifetimeValue;
    }

    public function setLegacyLifetimeValue(string $legacyLifetimeValue): static
    {
        $this->legacyLifetimeValue = $legacyLifetimeValue;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): static
    {
        $this->version = $version;
        return $this;
    }

    public function getLegacyCountInvoices(): ?int
    {
        return $this->legacyCountInvoices;
    }

    public function setLegacyCountInvoices(int $legacyCountInvoices): static
    {
        $this->legacyCountInvoices = $legacyCountInvoices;

        return $this;
    }

    public function getLegacyFirstSaleAmount(): ?string
    {
        return $this->legacyFirstSaleAmount;
    }

    public function setLegacyFirstSaleAmount(string $legacyFirstSaleAmount): static
    {
        $this->legacyFirstSaleAmount = $legacyFirstSaleAmount;

        return $this;
    }

    public function getLegacyFirstInvoicedAt(): ?DateTimeImmutable
    {
        return $this->legacyFirstInvoicedAt;
    }

    public function setLegacyFirstInvoicedAt(?DateTimeImmutable $legacyFirstInvoicedAt): static
    {
        $this->legacyFirstInvoicedAt = $legacyFirstInvoicedAt;

        return $this;
    }

    public function getLegacyLastInvoiceNumber(): ?string
    {
        return $this->legacyLastInvoiceNumber;
    }

    public function setLegacyLastInvoiceNumber(?string $legacyLastInvoiceNumber): static
    {
        $this->legacyLastInvoiceNumber = $legacyLastInvoiceNumber;

        return $this;
    }

    public function fromValueObject(CustomerObject $customerObject): static
    {
        $customerObject->populate();
        $this
            ->setActive($customerObject->isActive())
            ->setDeleted($customerObject->isDeleted())
            ->setName($customerObject->name)
            ->setHasInstallation($customerObject->hasInstallation)
            ->setHasSubscription($customerObject->hasSubscription)
            ->setLegacyLifetimeValue($customerObject->legacyLifetimeValue)
            ->setLegacyCountInvoices($customerObject->legacyCountInvoices)
            ->setLegacyFirstSaleAmount($customerObject->legacyFirstSaleAmount)
            ->setLegacyFirstInvoicedAt($customerObject->legacyFirstInvoicedAt)
            ->setLegacyLastInvoiceNumber($customerObject->legacyLastInvoiceNumber);

        return $this;
    }

    public function getInvoiceWithHighestTotal()
    {
        $invoices = $this->getInvoices()->toArray();
        usort($invoices, static function (Invoice $a, Invoice $b) {
            return (float)$b->getTotal() <=> (float)$a->getTotal();
        });

        return $invoices[0];
    }

    private function updateHasInstallation(): void
    {
        if (!$this->hasInvoices()) {
            return;
        }
        if ($this->hasInstallation === false) {
            $invoiceWithHighestTotal = $this->getInvoiceWithHighestTotal();
            if (
                $invoiceWithHighestTotal
                &&
                ((float) $invoiceWithHighestTotal->getTotal()
                >= self::INSTALLATION_THRESHOLD_FOR_INVOICE)
            ) {
                $this->hasInstallation = true;
            }
        }
    }

    public function isDoNotMail(): ?bool
    {
        return $this->prospect?->isDoNotMail();
    }
    public function setDoNotMail(bool $doNotMail): static
    {
        $this->prospect?->setDoNotMail($doNotMail);

        return $this;
    }
}

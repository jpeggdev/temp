<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\IsCertainPathTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\CompanyRepository;
use App\ValueObject\Roster\RosterCompany;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ORM\Table(name: 'company')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(fields: ['salesforceId'])]
#[ORM\UniqueConstraint(fields: ['intacctId'])]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\UniqueConstraint(
    name: 'unique_certain_path_company',
    columns: ['certain_path'],
    options: ['where' => '(certain_path = true)']
)]
class Company
{
    use TimestampableEntity;
    use IsCertainPathTrait;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $companyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $salesforceId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $intacctId = null;

    /**
     * @var Collection<int, Employee>
     */
    #[ORM\OneToMany(targetEntity: Employee::class, mappedBy: 'company')]
    private Collection $employeeRecords;

    #[ORM\Column(nullable: false, options: ['default' => 'false'])]
    private ?bool $marketingEnabled = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyEmail = null;

    #[ORM\ManyToOne(inversedBy: 'companies')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?FieldServiceSoftware $fieldServiceSoftware = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $websiteUrl = null;

    /**
     * @var Collection<int, CompanyTrade>
     */
    #[ORM\OneToMany(targetEntity: CompanyTrade::class, mappedBy: 'company')]
    private Collection $companyTrades;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $state = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = 'US';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $zipCode = null;

    #[ORM\Column(nullable: false, options: ['default' => 'false'])]
    private ?bool $isMailingAddressSame = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailingAddressLine1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailingAddressLine2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailingState = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailingCountry = 'US';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailingZipCode = null;

    /**
     * @var Collection<int, CompanyDataImportJob>
     */
    #[ORM\OneToMany(targetEntity: CompanyDataImportJob::class, mappedBy: 'company')]
    private Collection $companyDataImportJobs;

    /**
     * @var Collection<int, EventVoucher>
     */
    #[ORM\OneToMany(targetEntity: EventVoucher::class, mappedBy: 'company')]
    private Collection $vouchers;

    /**
     * @var Collection<int, EventCheckout>
     */
    #[ORM\OneToMany(targetEntity: EventCheckout::class, mappedBy: 'company')]
    private Collection $eventCheckouts;

    /**
     * @var Collection<int, Invoice>
     */
    #[ORM\OneToMany(targetEntity: Invoice::class, mappedBy: 'company')]
    private Collection $invoices;

    public function __construct()
    {
        $this->employeeRecords = new ArrayCollection();
        $this->companyTrades = new ArrayCollection();
        $this->companyDataImportJobs = new ArrayCollection();
        $this->vouchers = new ArrayCollection();
        $this->eventCheckouts = new ArrayCollection();
        $this->invoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getSalesforceId(): ?string
    {
        return $this->salesforceId;
    }

    public function setSalesforceId(?string $salesforceId): static
    {
        $this->salesforceId = $salesforceId;

        return $this;
    }

    public function getIntacctId(): ?string
    {
        return $this->intacctId;
    }

    public function setIntacctId(?string $intacctId): static
    {
        $this->intacctId = $intacctId;

        return $this;
    }

    /**
     * @return Collection<int, Employee>
     */
    public function getEmployeeRecords(): Collection
    {
        return $this->employeeRecords;
    }

    public function addEmployeeRecord(Employee $employeeRecord): static
    {
        if (!$this->employeeRecords->contains($employeeRecord)) {
            $this->employeeRecords->add($employeeRecord);
            $employeeRecord->setCompany($this);
        }

        return $this;
    }

    public function removeEmployeeRecord(Employee $employeeRecord): static
    {
        if ($this->employeeRecords->removeElement($employeeRecord)) {
            // set the owning side to null (unless already changed)
            if ($employeeRecord->getCompany() === $this) {
                $employeeRecord->setCompany(null);
            }
        }

        return $this;
    }

    public function isMarketingEnabled(): ?bool
    {
        return $this->marketingEnabled;
    }

    public function setMarketingEnabled(bool $marketingEnabled): static
    {
        $this->marketingEnabled = $marketingEnabled;

        return $this;
    }

    public function getCompanyEmail(): ?string
    {
        return $this->companyEmail;
    }

    public function setCompanyEmail(?string $companyEmail): static
    {
        $this->companyEmail = $companyEmail;

        return $this;
    }

    public function getFieldServiceSoftware(): ?FieldServiceSoftware
    {
        return $this->fieldServiceSoftware;
    }

    public function setFieldServiceSoftware(?FieldServiceSoftware $fieldServiceSoftware): static
    {
        $this->fieldServiceSoftware = $fieldServiceSoftware;

        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): static
    {
        $this->websiteUrl = $websiteUrl;

        return $this;
    }

    /**
     * @return Collection<int, CompanyTrade>
     */
    public function getCompanyTrades(): Collection
    {
        return $this->companyTrades;
    }

    public function addCompanyTrade(CompanyTrade $companyTrade): static
    {
        if (!$this->companyTrades->contains($companyTrade)) {
            $this->companyTrades->add($companyTrade);
            $companyTrade->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyTrade(CompanyTrade $companyTrade): static
    {
        if ($this->companyTrades->removeElement($companyTrade)) {
            // set the owning side to null (unless already changed)
            if ($companyTrade->getCompany() === $this) {
                $companyTrade->setCompany(null);
            }
        }

        return $this;
    }

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function setAddressLine1(?string $addressLine1): static
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function setAddressLine2(?string $addressLine2): static
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getCountry(): ?string
    {
        if (!$this->country) {
            return 'US';
        }

        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function isMailingAddressSame(): ?bool
    {
        return $this->isMailingAddressSame;
    }

    public function setMailingAddressSame(bool $isMailingAddressSame): static
    {
        $this->isMailingAddressSame = $isMailingAddressSame;

        return $this;
    }

    public function getMailingAddressLine1(): ?string
    {
        return $this->mailingAddressLine1;
    }

    public function setMailingAddressLine1(?string $mailingAddressLine1): static
    {
        $this->mailingAddressLine1 = $mailingAddressLine1;

        return $this;
    }

    public function getMailingAddressLine2(): ?string
    {
        return $this->mailingAddressLine2;
    }

    public function setMailingAddressLine2(?string $mailingAddressLine2): static
    {
        $this->mailingAddressLine2 = $mailingAddressLine2;

        return $this;
    }

    public function getMailingState(): ?string
    {
        return $this->mailingState;
    }

    public function setMailingState(?string $mailingState): static
    {
        $this->mailingState = $mailingState;

        return $this;
    }

    public function getMailingCountry(): ?string
    {
        if (!$this->mailingCountry) {
            return 'US';
        }

        return $this->mailingCountry;
    }

    public function setMailingCountry(?string $mailingCountry): static
    {
        $this->mailingCountry = $mailingCountry;

        return $this;
    }

    public function getMailingZipCode(): ?string
    {
        return $this->mailingZipCode;
    }

    public function setMailingZipCode(?string $mailingZipCode): static
    {
        $this->mailingZipCode = $mailingZipCode;

        return $this;
    }

    /**
     * @return Collection<int, CompanyDataImportJob>
     */
    public function getCompanyDataImportJobs(): Collection
    {
        return $this->companyDataImportJobs;
    }

    public function addCompanyDataImportJob(CompanyDataImportJob $companyDataImportJob): static
    {
        if (!$this->companyDataImportJobs->contains($companyDataImportJob)) {
            $this->companyDataImportJobs->add($companyDataImportJob);
            $companyDataImportJob->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyDataImportJob(CompanyDataImportJob $companyDataImportJob): static
    {
        if ($this->companyDataImportJobs->removeElement($companyDataImportJob)) {
            // set the owning side to null (unless already changed)
            if ($companyDataImportJob->getCompany() === $this) {
                $companyDataImportJob->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EventVoucher>
     */
    public function getVouchers(): Collection
    {
        return $this->vouchers;
    }

    public function addVoucher(EventVoucher $voucher): static
    {
        if (!$this->vouchers->contains($voucher)) {
            $this->vouchers->add($voucher);
            $voucher->setCompany($this);
        }

        return $this;
    }

    public function removeVoucher(EventVoucher $voucher): static
    {
        if ($this->vouchers->removeElement($voucher)) {
            // set the owning side to null (unless already changed)
            if ($voucher->getCompany() === $this) {
                $voucher->setCompany(null);
            }
        }

        return $this;
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
            $eventCheckout->setCompany($this);
        }

        return $this;
    }

    public function removeEventCheckout(EventCheckout $eventCheckout): static
    {
        if ($this->eventCheckouts->removeElement($eventCheckout)) {
            // set the owning side to null (unless already changed)
            if ($eventCheckout->getCompany() === $this) {
                $eventCheckout->setCompany(null);
            }
        }

        return $this;
    }

    public function setFieldsFromSalesforceRecord(RosterCompany $salesforceCompany): void
    {
        if ($salesforceCompany->isStochasticActive()) {
            // we only want to set it if it's true.
            // we don't necessarily want to flip it to false
            $this->setMarketingEnabled(true);
        }
        $this->setCompanyName($salesforceCompany->getName());
        $this->setSalesforceId($salesforceCompany->getSalesforceId());
        $this->setIntacctId($salesforceCompany->getIntacctId());
        $this->setCompanyEmail(
            $salesforceCompany->getPrimaryMemberEmail()
                ??
                $salesforceCompany->getIntacctContactEmail()
        );
        $this->setWebsiteUrl($salesforceCompany->getWebsite());
        $this->setAddressLine1($salesforceCompany->getBillingStreet());
        $this->setCity($salesforceCompany->getBillingCity());
        $this->setState($salesforceCompany->getBillingState());
        $this->setCountry($salesforceCompany->getBillingCountry());
        $this->setZipCode($salesforceCompany->getBillingPostalCode());
        $this->setMailingAddressLine1($salesforceCompany->getShippingStreet());
        $this->setMailingState($salesforceCompany->getShippingState());
        $this->setMailingCountry($salesforceCompany->getShippingCountry());
        $this->setMailingZipCode($salesforceCompany->getShippingPostalCode());
    }

    public function hasWebsiteUrl(): bool
    {
        return null !== $this->websiteUrl;
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
            $invoice->setCompany($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getCompany() === $this) {
                $invoice->setCompany(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\ValueObject\Roster;

class RosterCompany
{
    public const string ACTIVE = 'Active';
    private string $salesforceId;
    private string $ownerId;
    private string $intacctId;
    private string $name;
    private string $accountStatus;
    private ?string $primaryMemberName;
    private ?string $primaryMemberEmail;
    private ?string $intacctContactEmail;
    private ?string $intacctContactFirstName;
    private ?string $intacctContactLastName;
    private bool $hasSoftware;
    private ?string $stochasticMarketingStatus;
    private ?string $billingStreet;
    private ?string $billingCity;
    private ?string $billingState;
    private ?string $billingPostalCode;
    private ?string $billingCountry;
    private ?string $billingStateCode;
    private ?string $billingCountryCode;
    private ?string $shippingStreet;
    private ?string $shippingCity;
    private ?string $shippingState;
    private ?string $shippingPostalCode;
    private ?string $shippingCountry;
    private ?string $shippingStateCode;
    private ?string $shippingCountryCode;
    private ?string $website;
    private ?RosterCoach $coach = null;
    /** @var RosterEmployee[] */
    private array $employees = [];

    private function __construct(
        array $salesForceCompany,
        ?array $salesForceCoaches = null,
        ?array $salesForceEmployees = null,
    ) {
        $this->salesforceId = $salesForceCompany['Id'];
        $this->ownerId = $salesForceCompany['OwnerId'];
        $this->intacctId = $salesForceCompany['IntacctID__c'];
        $this->name = $salesForceCompany['Name'];
        $this->accountStatus = $salesForceCompany['Account_Status__c'];
        $this->primaryMemberName = $salesForceCompany['Primary_Member__c'];
        $this->primaryMemberEmail = $salesForceCompany['Primary_Member_Email__c'];
        $this->intacctContactEmail = $salesForceCompany['Intacct_Contact_Email__c'];
        $this->intacctContactFirstName = $salesForceCompany['Intacct_Contact_First_Name__c'];
        $this->intacctContactLastName = $salesForceCompany['Intacct_Contact_Last_Name__c'];
        $this->hasSoftware = $salesForceCompany['Software_Subscription__c'];
        $this->stochasticMarketingStatus = $salesForceCompany['Stochastic_Marketing_Status__c'];
        $this->billingStreet = $salesForceCompany['BillingStreet'];
        $this->billingCity = $salesForceCompany['BillingCity'];
        $this->billingState = $salesForceCompany['BillingState'];
        $this->billingPostalCode = $salesForceCompany['BillingPostalCode'];
        $this->billingCountry = $salesForceCompany['BillingCountry'];
        $this->billingStateCode = $salesForceCompany['BillingStateCode'];
        $this->billingCountryCode = $salesForceCompany['BillingCountryCode'];
        $this->shippingStreet = $salesForceCompany['ShippingStreet'];
        $this->shippingCity = $salesForceCompany['ShippingCity'];
        $this->shippingState = $salesForceCompany['ShippingState'];
        $this->shippingPostalCode = $salesForceCompany['ShippingPostalCode'];
        $this->shippingCountry = $salesForceCompany['ShippingCountry'];
        $this->shippingStateCode = $salesForceCompany['ShippingStateCode'];
        $this->shippingCountryCode = $salesForceCompany['ShippingCountryCode'];
        $this->website = $salesForceCompany['Website'];
        if (null !== $salesForceCoaches) {
            foreach ($salesForceCoaches as $salesForceCoach) {
                if ($salesForceCoach['Id'] === $this->getOwnerId()) {
                    $this->coach = RosterCoach::fromSalesforceCoach(
                        $salesForceCoach
                    );
                    break;
                }
            }
        }
        if (null !== $salesForceEmployees) {
            foreach ($salesForceEmployees as $salesForceEmployee) {
                if ($salesForceEmployee['AccountId'] === $this->getSalesforceId()) {
                    $this->employees[] = RosterEmployee::fromSalesforceEmployee(
                        $salesForceEmployee
                    );
                }
            }
        }
    }

    public static function fromSalesforceCompany(
        array $salesforceCompany,
    ): self {
        return new self(
            $salesforceCompany
        );
    }

    public static function fromSalesforceCompanyCoachEmployees(
        array $salesforceCompany,
        array $salesforceCoaches,
        array $salesforceEmployees,
    ): self {
        return new self(
            $salesforceCompany,
            $salesforceCoaches,
            $salesforceEmployees
        );
    }

    public function getSalesforceId(): string
    {
        return $this->salesforceId;
    }

    public function getOwnerId(): string
    {
        return $this->ownerId;
    }

    public function getIntacctId(): string
    {
        return $this->intacctId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAccountStatus(): string
    {
        return $this->accountStatus;
    }

    public function getPrimaryMemberName(): ?string
    {
        return $this->primaryMemberName;
    }

    public function getPrimaryMemberEmail(): ?string
    {
        return $this->primaryMemberEmail;
    }

    public function getIntacctContactEmail(): ?string
    {
        return $this->intacctContactEmail;
    }

    public function getIntacctContactFirstName(): ?string
    {
        return $this->intacctContactFirstName;
    }

    public function getIntacctContactLastName(): ?string
    {
        return $this->intacctContactLastName;
    }

    public function hasSoftware(): bool
    {
        return $this->hasSoftware;
    }

    public function getStochasticMarketingStatus(): ?string
    {
        return $this->stochasticMarketingStatus;
    }

    public function getBillingStreet(): ?string
    {
        return $this->billingStreet;
    }

    public function getBillingCity(): ?string
    {
        return $this->billingCity;
    }

    public function getBillingState(): ?string
    {
        return $this->billingState;
    }

    public function getBillingPostalCode(): ?string
    {
        return $this->billingPostalCode;
    }

    public function getBillingCountry(): ?string
    {
        return $this->billingCountry;
    }

    public function getBillingStateCode(): ?string
    {
        return $this->billingStateCode;
    }

    public function getBillingCountryCode(): ?string
    {
        return $this->billingCountryCode;
    }

    public function getShippingStreet(): ?string
    {
        return $this->shippingStreet;
    }

    public function getShippingCity(): ?string
    {
        return $this->shippingCity;
    }

    public function getShippingState(): ?string
    {
        return $this->shippingState;
    }

    public function getShippingPostalCode(): ?string
    {
        return $this->shippingPostalCode;
    }

    public function getShippingCountry(): ?string
    {
        return $this->shippingCountry;
    }

    public function getShippingStateCode(): ?string
    {
        return $this->shippingStateCode;
    }

    public function getShippingCountryCode(): ?string
    {
        return $this->shippingCountryCode;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function getCoach(): ?RosterCoach
    {
        return $this->coach;
    }

    /**
     * @return RosterEmployee[]
     */
    public function getEmployees(): array
    {
        return $this->employees;
    }

    public function isStochasticActive(): bool
    {
        return self::ACTIVE === $this->getStochasticMarketingStatus();
    }
}

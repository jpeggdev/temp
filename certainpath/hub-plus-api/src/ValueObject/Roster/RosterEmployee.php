<?php

namespace App\ValueObject\Roster;

class RosterEmployee
{
    private string $salesforceId;
    private ?string $firstName;
    private string $lastName;
    private ?string $email;
    private ?string $phone;
    private ?string $title;
    private string $contactType;
    private string $accountStatus;
    private bool $inactive;
    private ?string $ssoId;
    private bool $hubAccountSuspended;
    private ?string $hubUserType;
    private bool $hubAccount;
    private string $intacctId;
    private ?string $reportsToId;

    private function __construct(
        array $salesforceEmployee,
    ) {
        $this->salesforceId = $salesforceEmployee['Id'];
        $this->firstName = $salesforceEmployee['FirstName'];
        $this->lastName = $salesforceEmployee['LastName'];
        $this->email = $salesforceEmployee['Email'];
        $this->phone = $salesforceEmployee['Phone'];
        $this->title = $salesforceEmployee['Title'];
        $this->contactType = $salesforceEmployee['Contact_Type__c'];
        $this->accountStatus = $salesforceEmployee['Account_status__c'];
        $this->inactive = (bool) $salesforceEmployee['Inactive_Contact__c'];
        $this->ssoId = $salesforceEmployee['SSO_ID__c'];
        $this->hubAccountSuspended = (bool) $salesforceEmployee['HUB_Account_Suspended__c'];
        $this->hubUserType = $salesforceEmployee['HUB_User_Type__c'];
        $this->hubAccount = (bool) $salesforceEmployee['HUB_Account__c'];
        $this->intacctId = $salesforceEmployee['IntacctID_Contact__c'];
        $this->reportsToId = $salesforceEmployee['ReportsToId'];
    }

    public static function fromSalesforceEmployee(array $salesforceEmployee): self
    {
        return new self(
            $salesforceEmployee
        );
    }

    public function getSalesforceId(): string
    {
        return $this->salesforceId;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName ?? '';
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getContactType(): string
    {
        return $this->contactType;
    }

    public function getAccountStatus(): string
    {
        return $this->accountStatus;
    }

    public function isInactive(): bool
    {
        return $this->inactive;
    }

    public function getSsoId(): ?string
    {
        return $this->ssoId;
    }

    public function isHubAccountSuspended(): bool
    {
        return $this->hubAccountSuspended;
    }

    public function getHubUserType(): ?string
    {
        return $this->hubUserType;
    }

    public function isHubAccount(): bool
    {
        return $this->hubAccount;
    }

    public function getIntacctId(): string
    {
        return $this->intacctId;
    }

    public function getReportsToId(): ?string
    {
        return $this->reportsToId;
    }

    public function hasEmail(): bool
    {
        return null !== $this->email;
    }
}

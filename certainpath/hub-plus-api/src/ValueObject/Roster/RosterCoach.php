<?php

namespace App\ValueObject\Roster;

class RosterCoach
{
    private string $salesforceId;
    private string $companyName;
    private ?string $department;
    private string $title;
    private string $username;
    private string $name;
    private string $firstName;
    private string $lastName;
    private ?string $phone;
    private string $email;

    private function __construct(
        array $salesforceCoach,
    ) {
        $this->salesforceId = $salesforceCoach['Id'];
        $this->companyName = $salesforceCoach['CompanyName'];
        $this->department = $salesforceCoach['Department'];
        $this->title = $salesforceCoach['Title'];
        $this->username = $salesforceCoach['Username'];
        $this->name = $salesforceCoach['Name'];
        $this->firstName = $salesforceCoach['FirstName'];
        $this->lastName = $salesforceCoach['LastName'];
        $this->phone = $salesforceCoach['Phone'];
        $this->email = $salesforceCoach['Email'];
    }

    public static function fromSalesforceCoach(
        mixed $salesforceCoach,
    ): self {
        return new self(
            $salesforceCoach
        );
    }

    public function getSalesforceId(): string
    {
        return $this->salesforceId;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}

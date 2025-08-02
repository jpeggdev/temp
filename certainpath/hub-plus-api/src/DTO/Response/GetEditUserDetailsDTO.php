<?php

namespace App\DTO\Response;

class GetEditUserDetailsDTO
{
    public string $firstName;
    public string $lastName;
    public string $email;
    public string $employeeUuid;

    public array $availableApplications = [];
    public array $availableRoles = [];
    public array $availablePermissions = [];
    public array $employeeApplicationAccess = [];
    public ?int $employeeBusinessRoleId;
    public array $employeeRolePermissions = [];
    public array $employeeAdditionalPermissions = [];

    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $employeeUuid,
        ?int $employeeBusinessRoleId = null,
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->employeeUuid = $employeeUuid;
        $this->employeeBusinessRoleId = $employeeBusinessRoleId; // Set the business role ID here
    }

    public function setAvailableApplications(array $applications): void
    {
        $this->availableApplications = $applications;
    }

    public function setAvailableRoles(array $roles): void
    {
        $this->availableRoles = $roles;
    }

    public function setAvailablePermissions(array $permissions): void
    {
        $this->availablePermissions = $permissions;
    }

    public function setEmployeeApplicationAccess(array $accessRecords): void
    {
        $this->employeeApplicationAccess = $accessRecords;
    }

    public function setEmployeeRolePermissions(array $permissions): void
    {
        $this->employeeRolePermissions = $permissions;
    }

    public function setEmployeeAdditionalPermissions(array $permissions): void
    {
        $this->employeeAdditionalPermissions = $permissions;
    }
}

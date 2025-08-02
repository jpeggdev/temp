<?php

namespace App\DTO\Response;

class GetMyUserProfileResponseDTO
{
    public ?string $firstName;
    public ?string $lastName;
    public ?string $workEmail;
    public ?string $employeeUuid;

    public function __construct(
        ?string $firstName,
        ?string $lastName,
        ?string $workEmail,
        ?string $employeeUuid,
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->workEmail = $workEmail;
        $this->employeeUuid = $employeeUuid;
    }
}

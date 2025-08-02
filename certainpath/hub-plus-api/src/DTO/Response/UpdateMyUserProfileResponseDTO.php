<?php

namespace App\DTO\Response;

class UpdateMyUserProfileResponseDTO
{
    public string $firstName;
    public string $lastName;
    public string $email;
    public string $employeeUuid;

    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $employeeUuid,
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->employeeUuid = $employeeUuid;
    }
}

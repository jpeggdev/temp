<?php

namespace App\DTO\Response;

class CreateUserResponseDTO
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $employeeUuid,
        public ?string $salesforceId = null,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Response\Employee;

use App\Entity\Employee;

class EditEmployeeResponseDTO
{
    public function __construct(
        public string $uuid,
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?string $salesforceId,
        public ?string $ssoId,
    ) {
    }

    public static function fromEntity(Employee $employee): self
    {
        return new self(
            $employee->getUuid(),
            $employee->getFirstName(),
            $employee->getLastName(),
            $employee->getUser()->getEmail(),
            $employee->getUser()->getSalesforceId(),
            $employee->getUser()->getSsoId()
        );
    }
}

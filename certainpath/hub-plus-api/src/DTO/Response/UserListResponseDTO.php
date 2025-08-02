<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\Employee;
use App\Entity\User;

class UserListResponseDTO
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $uuid,
        public ?string $salesforceId = null,
        public ?string $employeeUuid = null,
    ) {
    }

    public static function fromEntity(User $user, Employee $employee): self
    {
        return new self(
            $user->getId(),
            $employee->getFirstName(),
            $employee->getLastName(),
            $user->getEmail(),
            $user->getUuid(),
            $user->getSalesforceId(),
            $employee->getUuid()
        );
    }
}

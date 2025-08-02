<?php

declare(strict_types=1);

namespace App\DTO\Response\EmployeeRole;

class CreateUpdateEmployeeRoleResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $name,
    ) {
    }
}

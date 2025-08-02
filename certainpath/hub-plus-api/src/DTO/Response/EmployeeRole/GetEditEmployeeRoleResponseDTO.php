<?php

declare(strict_types=1);

namespace App\DTO\Response\EmployeeRole;

class GetEditEmployeeRoleResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $name,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Request\EmployeeRole;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUpdateEmployeeRoleDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Employee role name cannot be empty')]
        public string $name,
    ) {
    }
}

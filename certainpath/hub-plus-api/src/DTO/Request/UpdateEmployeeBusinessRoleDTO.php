<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateEmployeeBusinessRoleDTO
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Positive]
        public int $businessRoleId,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Request\Employee;

use Symfony\Component\Validator\Constraints as Assert;

readonly class EditEmployeeDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $firstName,
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $lastName,
    ) {
    }
}

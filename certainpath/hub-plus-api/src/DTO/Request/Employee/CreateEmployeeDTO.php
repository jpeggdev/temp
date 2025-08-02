<?php

declare(strict_types=1);

namespace App\DTO\Request\Employee;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateEmployeeDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $firstName,
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $lastName,
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
    ) {
    }
}

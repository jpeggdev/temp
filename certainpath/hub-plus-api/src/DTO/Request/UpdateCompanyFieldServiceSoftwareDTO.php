<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateCompanyFieldServiceSoftwareDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $fieldServiceSoftwareId,
    ) {
    }
}

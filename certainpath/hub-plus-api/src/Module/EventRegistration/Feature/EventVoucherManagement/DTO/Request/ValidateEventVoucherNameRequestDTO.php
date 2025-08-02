<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ValidateEventVoucherNameRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        #[Assert\Type(type: 'string', message: 'The code field must be a string.')]
        public string $name,
    ) {
    }
}

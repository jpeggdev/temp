<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Response;

readonly class ValidateEventDiscountCodeResponseDTO
{
    public function __construct(
        public bool $codeExists,
        public ?string $message = null,
    ) {
    }
}

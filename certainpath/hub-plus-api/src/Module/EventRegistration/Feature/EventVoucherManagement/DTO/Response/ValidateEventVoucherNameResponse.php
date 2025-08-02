<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Response;

class ValidateEventVoucherNameResponse
{
    public function __construct(
        public bool $nameExists,
        public ?string $message = null,
    ) {
    }
}

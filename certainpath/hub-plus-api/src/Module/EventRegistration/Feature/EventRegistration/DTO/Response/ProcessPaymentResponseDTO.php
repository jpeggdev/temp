<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Response;

final class ProcessPaymentResponseDTO
{
    public function __construct(
        public ?string $transactionId,
        public bool $success,
    ) {
    }
}

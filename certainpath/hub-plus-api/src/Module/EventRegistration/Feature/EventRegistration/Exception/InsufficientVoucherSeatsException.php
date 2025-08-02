<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

class InsufficientVoucherSeatsException extends \RuntimeException
{
    public function __construct(
        string $message = 'Insufficient company voucher seats available.',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

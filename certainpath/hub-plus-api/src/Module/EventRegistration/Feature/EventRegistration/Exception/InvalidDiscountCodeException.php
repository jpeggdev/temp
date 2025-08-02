<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

class InvalidDiscountCodeException extends \RuntimeException
{
    public function __construct(
        string $message = 'Invalid or unavailable discount code.',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

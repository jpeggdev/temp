<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

class DiscountNotValidForEventException extends \RuntimeException
{
    public function __construct(
        string $message = 'Discount code not valid for this event.',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

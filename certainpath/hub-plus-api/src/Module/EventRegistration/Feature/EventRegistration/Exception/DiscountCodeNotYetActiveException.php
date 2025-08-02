<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

class DiscountCodeNotYetActiveException extends \RuntimeException
{
    public function __construct(
        string $message = 'Discount code not yet active.',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

class MinimumPurchaseNotMetException extends \RuntimeException
{
    public function __construct(
        string $message = 'Minimum purchase amount not met for discount.',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

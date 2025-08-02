<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

class DiscountReachedMaxUsageException extends \RuntimeException
{
    public function __construct(
        string $message = 'This discount code has reached its maximum usage.',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

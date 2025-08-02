<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

class EventNotEligibleForVoucherException extends \RuntimeException
{
    public function __construct(
        string $message = 'This event is not voucher-eligible, so vouchers cannot be redeemed.',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

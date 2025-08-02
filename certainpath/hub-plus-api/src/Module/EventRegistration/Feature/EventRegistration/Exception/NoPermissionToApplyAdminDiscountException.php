<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

class NoPermissionToApplyAdminDiscountException extends \RuntimeException
{
    public function __construct(
        string $message = 'You do not have permission to apply an admin discount.',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

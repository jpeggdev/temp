<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class EventVoucherUpdateException extends HttpException
{
    private string $defaultMessage = 'Failed to update the voucher.';

    public function __construct(
        int $statusCode = 500,
        ?string $message = null,
        ?\Throwable $previous = null,
        array $headers = [],
        int $code = 0,
    ) {
        $fullMessage = $message
            ? sprintf('%s %s', $this->defaultMessage, $message)
            : $this->defaultMessage;

        parent::__construct($statusCode, $fullMessage, $previous, $headers, $code);
    }
}

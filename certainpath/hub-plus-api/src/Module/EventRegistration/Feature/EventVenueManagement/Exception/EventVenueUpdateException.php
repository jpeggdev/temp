<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class EventVenueUpdateException extends HttpException
{
    private string $defaultMessage = 'Failed to update the venue.';

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

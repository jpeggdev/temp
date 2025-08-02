<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventVenueNotFoundException extends NotFoundHttpException
{
    protected string $defaultMessage = 'Event Venue not found.';

    public function __construct(
        ?string $message = null,
        ?\Throwable $previous = null,
        int $code = 0,
    ) {
        parent::__construct(
            $message ?? $this->defaultMessage,
            $previous,
            $code ?: Response::HTTP_NOT_FOUND
        );
    }
}

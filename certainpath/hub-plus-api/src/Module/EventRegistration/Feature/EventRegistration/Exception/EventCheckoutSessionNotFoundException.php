<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventCheckoutSessionNotFoundException extends NotFoundHttpException
{
    public function __construct(
        string $message = 'Invalid or non-existent Event Checkout Session UUID.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $previous);
    }
}

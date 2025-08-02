<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventCheckoutNotFoundException extends NotFoundHttpException
{
    public function __construct(string $message = 'Event Checkout not found', ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}

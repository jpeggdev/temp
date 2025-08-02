<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DiscountCodeExpiredException extends BadRequestHttpException
{
    public function __construct(
        string $message = 'This discount code has expired.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $previous);
    }
}

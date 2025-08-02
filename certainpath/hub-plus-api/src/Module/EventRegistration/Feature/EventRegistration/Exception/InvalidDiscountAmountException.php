<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InvalidDiscountAmountException extends BadRequestHttpException
{
    public function __construct(
        string $message = 'Discount amount is zero or invalid.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $previous);
    }
}

<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DiscountCodeNotFoundException extends NotFoundHttpException
{
    public function __construct(
        string $message = 'Discount code not found.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $previous);
    }
}

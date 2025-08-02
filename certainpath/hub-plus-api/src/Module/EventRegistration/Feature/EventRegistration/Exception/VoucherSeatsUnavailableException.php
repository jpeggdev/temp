<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class VoucherSeatsUnavailableException extends BadRequestHttpException
{
    public function __construct(
        string $message = 'Not enough combined voucher seats available.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $previous);
    }
}

<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VoucherNotFoundException extends NotFoundHttpException
{
    public function __construct(
        string $message = 'No active vouchers associated with this company',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $previous);
    }
}

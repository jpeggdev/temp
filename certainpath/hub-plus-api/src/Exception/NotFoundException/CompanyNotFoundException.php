<?php

declare(strict_types=1);

namespace App\Exception\NotFoundException;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanyNotFoundException extends NotFoundHttpException
{
    public function __construct(
        string $message = 'Company with the provided UUID not found',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $previous);
    }
}

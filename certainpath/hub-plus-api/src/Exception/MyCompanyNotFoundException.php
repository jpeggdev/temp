<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MyCompanyNotFoundException extends NotFoundHttpException
{
    public function __construct(
        string $message = 'Company not found for the current user',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $previous);
    }
}

<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FieldServiceSoftwareNotFoundException extends NotFoundHttpException
{
    public function __construct(string $message = 'Field service software not found', ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}

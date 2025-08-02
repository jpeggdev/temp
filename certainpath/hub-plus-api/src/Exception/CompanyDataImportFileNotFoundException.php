<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanyDataImportFileNotFoundException extends NotFoundHttpException
{
    public function __construct(string $message = 'Company Data Import File not found', ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}

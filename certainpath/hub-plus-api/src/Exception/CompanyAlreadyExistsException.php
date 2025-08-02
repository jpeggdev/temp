<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CompanyAlreadyExistsException extends ConflictHttpException
{
    public function __construct(string $message = 'Company already exists.', ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message, $previous, $code);
    }
}

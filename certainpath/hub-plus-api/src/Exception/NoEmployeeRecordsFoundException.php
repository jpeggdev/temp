<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NoEmployeeRecordsFoundException extends NotFoundHttpException
{
    public function __construct(string $message = 'User does not have any associated employee records.', ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }

    public function getMessageKey(): string
    {
        return 'User does not have any associated employee records.';
    }
}

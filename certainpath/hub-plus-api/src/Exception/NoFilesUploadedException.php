<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class NoFilesUploadedException extends BadRequestHttpException
{
    /** @var int */
    protected $code = 400; // HTTP Bad Request status code
    protected string $defaultMessage = 'No files were uploaded';

    public function __construct(?string $message = null, ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message ?? $this->defaultMessage, $previous, $code ?: $this->code);
    }
}

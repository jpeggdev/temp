<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class FileUploadFailedException extends HttpException
{
    /** @var int */
    protected $code = 500; // HTTP Internal Server Error status code
    protected string $defaultMessage = 'Failed to upload files.';

    public function __construct(?string $message = null, ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct($this->code, $message ?? $this->defaultMessage, $previous, [], $code ?: $this->code);
    }
}

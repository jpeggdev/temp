<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InvalidFileTypeException extends BadRequestHttpException
{
    /** @var int */
    protected $code = 400; // HTTP Bad Request status code
    protected string $defaultMessage = 'Invalid file type. Only PDF, DOC, DOCX, PPT, and PPTX files are allowed.';

    public function __construct(?string $message = null, ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message ?? $this->defaultMessage, $previous, $code ?: $this->code);
    }
}

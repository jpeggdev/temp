<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventNotFoundException extends NotFoundHttpException
{
    /** @var int */
    protected $code = Response::HTTP_NOT_FOUND;
    protected string $defaultMessage = 'Event not found';

    public function __construct(?string $message = null, ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message ?? $this->defaultMessage, $previous, $code ?: $this->code);
    }
}

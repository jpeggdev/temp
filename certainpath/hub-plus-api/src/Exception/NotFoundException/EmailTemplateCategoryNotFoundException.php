<?php

declare(strict_types=1);

namespace App\Exception\NotFoundException;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EmailTemplateCategoryNotFoundException extends NotFoundHttpException
{
    protected string $defaultMessage = 'Email template category not found.';

    public function __construct(
        ?string $message = null,
        ?\Throwable $previous = null,
        int $code = 0,
    ) {
        parent::__construct(
            $message ?? $this->defaultMessage,
            $previous,
            $code ?: Response::HTTP_NOT_FOUND
        );
    }
}

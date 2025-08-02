<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\Shared\DiscountType\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DiscountTypeNotFoundException extends NotFoundHttpException
{
    protected string $defaultMessage = 'Discount Type not found.';

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

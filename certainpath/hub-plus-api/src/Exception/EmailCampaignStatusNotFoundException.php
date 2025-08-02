<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EmailCampaignStatusNotFoundException extends NotFoundHttpException
{
    protected string $defaultMessage = 'Email campaign status not found';

    public function __construct(
        ?string $message = null,
        ?\Throwable $previous = null,
        ?int $code = 0,
    ) {
        $message = $message ?? $this->defaultMessage;
        $code = $code ?? Response::HTTP_NOT_FOUND;

        parent::__construct(
            $message,
            $previous,
            $code
        );
    }
}

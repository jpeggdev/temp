<?php

namespace App\Exception;

class CampaignStopException extends UnificationAPIException
{
    protected int $statusCode = 400;

    public function __construct(
        string $message = 'Failed to stop campaign.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $this->statusCode, $previous);
    }
}

<?php

namespace App\Exception;

class CampaignResumeException extends UnificationAPIException
{
    protected int $statusCode = 400;

    public function __construct(
        string $message = 'Failed to resume campaign.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $this->statusCode, $previous);
    }
}

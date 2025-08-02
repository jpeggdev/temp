<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\Uploads\Exception;

use App\Exception\UnificationAPIException;

class UploadDoNotMailListException extends UnificationAPIException
{
    public function __construct(
        string $message = 'Failed to upload restricted addresses.',
        int $statusCode = 500,
        ?\Exception $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}

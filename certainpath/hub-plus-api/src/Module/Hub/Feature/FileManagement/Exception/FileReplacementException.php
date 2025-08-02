<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Exception;

class FileReplacementException extends FileManagementException
{
    public function __construct(
        string $message = 'An error occurred during file replacement.',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

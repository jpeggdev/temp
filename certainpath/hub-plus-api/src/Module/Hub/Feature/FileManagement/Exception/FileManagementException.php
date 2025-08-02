<?php

namespace App\Module\Hub\Feature\FileManagement\Exception;

class FileManagementException extends \Exception
{
    public function __construct(
        string $message = 'An error occurred during file management operation.',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

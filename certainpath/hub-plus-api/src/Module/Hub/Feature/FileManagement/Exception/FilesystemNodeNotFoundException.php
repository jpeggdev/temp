<?php

namespace App\Module\Hub\Feature\FileManagement\Exception;

class FilesystemNodeNotFoundException extends FileManagementException
{
    public function __construct(
        string $message = 'Filesystem node not found.',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

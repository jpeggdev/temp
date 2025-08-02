<?php

namespace App\Module\Hub\Feature\FileManagement\Exception;

class TagNotFoundException extends FileManagementException
{
    public function __construct(string $message = 'Tag not found.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

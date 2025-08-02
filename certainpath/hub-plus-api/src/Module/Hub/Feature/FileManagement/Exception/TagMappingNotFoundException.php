<?php

namespace App\Module\Hub\Feature\FileManagement\Exception;

class TagMappingNotFoundException extends FileManagementException
{
    public function __construct(string $message = 'Tag mapping not found.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

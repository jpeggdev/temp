<?php

namespace App\Module\Hub\Feature\FileManagement\Exception;

class DuplicateTagMappingException extends FileManagementException
{
    public function __construct(
        string $message = 'This tag is already assigned to the node.',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

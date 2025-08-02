<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Exception;

class FileInUseException extends FolderOperationException
{
    public function __construct(
        string $message = 'Cannot delete file because it is being used by other items in the system.',
    ) {
        parent::__construct($message);
    }
}

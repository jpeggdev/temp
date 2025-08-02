<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Exception;

class NonEmptyFolderException extends FolderOperationException
{
    public function __construct(string $message = 'Cannot delete folder because it contains files or subfolders. '.
    'Please delete the contents first.')
    {
        parent::__construct($message);
    }
}

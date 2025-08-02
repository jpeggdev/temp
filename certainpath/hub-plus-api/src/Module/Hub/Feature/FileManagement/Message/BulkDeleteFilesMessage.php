<?php

namespace App\Module\Hub\Feature\FileManagement\Message;

readonly class BulkDeleteFilesMessage
{
    public function __construct(
        private string $jobUuid,
    ) {
    }

    public function getJobUuid(): string
    {
        return $this->jobUuid;
    }
}

<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

readonly class BulkDeleteNodesQueueResponseDTO
{
    public function __construct(
        public string $jobId,
        public string $status,
        public int $totalFiles,
        public bool $success = true,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

readonly class DeleteTagResponseDTO
{
    public function __construct(
        public string $message,
        public int $deletedTagId,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

readonly class GetPresignedUrlsResponseDTO
{
    /**
     * @param array<string, string> $presignedUrls Array of fileUuid => presignedUrl pairs
     */
    public function __construct(
        public array $presignedUrls,
    ) {
    }
}

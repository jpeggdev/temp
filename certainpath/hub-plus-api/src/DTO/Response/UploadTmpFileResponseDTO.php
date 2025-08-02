<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class UploadTmpFileResponseDTO
{
    public function __construct(
        public int $fileId,
        public string $fileUrl,
        public string $name,
        public string $fileUuid,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

readonly class UploadFilesystemNodesResponseDTO
{
    /**
     * @param array<array{
     *     uuid: string,
     *     name: string,
     *     fileType: string,
     *     type: string,
     *     parentUuid: ?string,
     *     createdAt: string,
     *     updatedAt: string,
     *     tags: array,
     *     mimeType: ?string,
     *     fileSize: ?int,
     *     url: ?string
     * }> $files
     */
    public function __construct(
        public array $files,
    ) {
    }
}

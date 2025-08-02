<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

readonly class GetFileManagerMetaDataResponseDTO
{
    /**
     * @param TagStatDTO[]      $tags
     * @param FileTypeStatDTO[] $fileTypes
     */
    public function __construct(
        public array $tags,
        public array $fileTypes,
    ) {
    }
}

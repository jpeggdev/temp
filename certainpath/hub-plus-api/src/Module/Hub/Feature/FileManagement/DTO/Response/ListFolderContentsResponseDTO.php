<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

use App\Entity\Folder;

readonly class ListFolderContentsResponseDTO
{
    /**
     * @param FilesystemNodeResponseDTO[] $items
     */
    public function __construct(
        public array $items,
        public int $totalCount,
        public ?FolderInfoDTO $currentFolder = null,
    ) {
    }

    public static function create(
        array $items,
        int $totalCount,
        ?Folder $currentFolder = null,
    ): self {
        $folderInfo = FolderInfoDTO::fromEntity($currentFolder);

        return new self(
            items: $items,
            totalCount: $totalCount,
            currentFolder: $folderInfo
        );
    }
}

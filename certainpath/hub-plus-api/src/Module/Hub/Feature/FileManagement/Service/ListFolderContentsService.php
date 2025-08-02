<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Entity\Folder;
use App\Module\Hub\Feature\FileManagement\DTO\Request\ListFolderContentsRequestDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\BreadcrumbItemDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\FilesystemNodeResponseDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\FolderInfoDTO;
use App\Module\Hub\Feature\FileManagement\Exception\FolderOperationException;
use App\Repository\FilesystemNodeRepository;
use App\Repository\FolderRepository;

readonly class ListFolderContentsService
{
    public function __construct(
        private FilesystemNodeRepository $filesystemNodeRepository,
        private FolderRepository $folderRepository,
    ) {
    }

    public function listContents(ListFolderContentsRequestDTO $dto): array
    {
        $folder = null;
        if ($dto->folderUuid) {
            $folder = $this->folderRepository->findOneByUuid($dto->folderUuid);
            if (!$folder) {
                throw new FolderOperationException('Folder not found.');
            }
        }

        $result = $this->filesystemNodeRepository->getFolderContents($folder, $dto);

        $items = FilesystemNodeResponseDTO::fromEntities($result['items']);
        $folderDTO = FolderInfoDTO::fromEntity($folder);

        $breadcrumbs = [];
        $currentFolder = $folder;

        while (null !== $currentFolder) {
            array_unshift($breadcrumbs, BreadcrumbItemDTO::fromEntity($currentFolder));
            $currentFolder = $currentFolder->getParent();
        }

        $nextCursor = null;
        if (!empty($result['items']) && $result['hasMore']) {
            $lastItem = end($result['items']);
            $cursorData = [
                'uuid' => $lastItem->getUuid(),
                'nodeType' => $lastItem instanceof Folder ? 0 : 1,
                'sortValue' => $this->filesystemNodeRepository->extractSortValue($lastItem, $dto->sortBy),
            ];
            $nextCursor = base64_encode(json_encode($cursorData));
        }

        return [
            'data' => [
                'items' => $items,
                'currentFolder' => $folderDTO,
                'breadcrumbs' => $breadcrumbs,
                'nextCursor' => $nextCursor,
            ],
            'total' => $result['total'],
            'hasMore' => $result['hasMore'],
        ];
    }
}

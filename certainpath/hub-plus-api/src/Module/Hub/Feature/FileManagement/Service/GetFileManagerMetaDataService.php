<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Module\Hub\Feature\FileManagement\DTO\Response\FileTypeStatDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\GetFileManagerMetaDataResponseDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\TagStatDTO;
use App\Repository\FilesystemNodeRepository;
use App\Repository\FileSystemNodeTagRepository;
use Doctrine\DBAL\Exception;

readonly class GetFileManagerMetaDataService
{
    public function __construct(
        private FileSystemNodeTagRepository $tagRepository,
        private FilesystemNodeRepository $nodeRepository,
    ) {
    }

    public function getMetaData(): GetFileManagerMetaDataResponseDTO
    {
        $tags = $this->getAllTagsWithCount();
        $fileTypes = $this->getFileTypesWithCount();

        return new GetFileManagerMetaDataResponseDTO(
            tags: $tags,
            fileTypes: $fileTypes,
        );
    }

    /**
     * @return TagStatDTO[]
     *
     * @throws Exception
     */
    private function getAllTagsWithCount(): array
    {
        $tagData = $this->tagRepository->findAllTagsWithCount();

        return array_map(fn ($data) => TagStatDTO::fromTagWithCount($data), $tagData);
    }

    /**
     * @return FileTypeStatDTO[]
     *
     * @throws Exception
     */
    private function getFileTypesWithCount(): array
    {
        $typeData = $this->nodeRepository->findAllFileTypesWithCount();

        return array_map(fn ($data) => FileTypeStatDTO::fromTypeWithCount($data), $typeData);
    }
}

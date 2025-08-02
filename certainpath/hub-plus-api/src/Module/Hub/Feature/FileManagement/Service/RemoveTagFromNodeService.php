<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Module\Hub\Feature\FileManagement\DTO\Request\RemoveTagFromNodeRequestDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\RemoveTagFromNodeResponseDTO;
use App\Module\Hub\Feature\FileManagement\Exception\FilesystemNodeNotFoundException;
use App\Module\Hub\Feature\FileManagement\Exception\TagMappingNotFoundException;
use App\Repository\FilesystemNodeRepository;
use App\Repository\FileSystemNodeTagMappingRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class RemoveTagFromNodeService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FilesystemNodeRepository $filesystemNodeRepository,
        private FileSystemNodeTagMappingRepository $mappingRepository,
    ) {
    }

    public function removeTagFromNode(RemoveTagFromNodeRequestDTO $dto): RemoveTagFromNodeResponseDTO
    {
        $filesystemNode = $this->filesystemNodeRepository->findOneByUuid($dto->filesystemNodeUuid);
        if (!$filesystemNode) {
            throw new FilesystemNodeNotFoundException();
        }

        $mapping = $this->mappingRepository->findOneBy([
            'fileSystemNode' => $filesystemNode->getId(),
            'fileSystemNodeTag' => $dto->tagId,
        ]);

        if (!$mapping) {
            throw new TagMappingNotFoundException();
        }

        $tagName = $mapping->getFileSystemNodeTag()->getName();
        $nodeUuid = $filesystemNode->getUuid();

        $this->em->remove($mapping);
        $this->em->flush();

        return new RemoveTagFromNodeResponseDTO(
            message: "Tag '$tagName' removed from node successfully",
            tagId: $dto->tagId,
            filesystemNodeUuid: $nodeUuid
        );
    }
}

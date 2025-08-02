<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Entity\FileSystemNodeTagMapping;
use App\Module\Hub\Feature\FileManagement\DTO\Request\AssignTagToNodeRequestDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\AssignTagToNodeResponseDTO;
use App\Module\Hub\Feature\FileManagement\Exception\DuplicateTagMappingException;
use App\Module\Hub\Feature\FileManagement\Exception\FilesystemNodeNotFoundException;
use App\Module\Hub\Feature\FileManagement\Exception\TagNotFoundException;
use App\Repository\FilesystemNodeRepository;
use App\Repository\FileSystemNodeTagMappingRepository;
use App\Repository\FileSystemNodeTagRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class AssignTagToNodeService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FilesystemNodeRepository $filesystemNodeRepository,
        private FileSystemNodeTagRepository $tagRepository,
        private FileSystemNodeTagMappingRepository $mappingRepository,
    ) {
    }

    public function assignTagToNode(AssignTagToNodeRequestDTO $dto): AssignTagToNodeResponseDTO
    {
        $filesystemNode = $this->filesystemNodeRepository->findOneByUuid($dto->filesystemNodeUuid);
        if (!$filesystemNode) {
            throw new FilesystemNodeNotFoundException();
        }

        $tag = $this->tagRepository->find($dto->tagId);
        if (!$tag) {
            throw new TagNotFoundException();
        }

        $existingMapping = $this->mappingRepository->findOneBy([
            'fileSystemNode' => $filesystemNode->getId(),
            'fileSystemNodeTag' => $tag->getId(),
        ]);

        if ($existingMapping) {
            throw new DuplicateTagMappingException();
        }

        $mapping = new FileSystemNodeTagMapping();
        $mapping->setFileSystemNodeTag($tag);
        $mapping->setFileSystemNode($filesystemNode);

        $this->em->persist($mapping);
        $this->em->flush();

        return new AssignTagToNodeResponseDTO(
            mappingId: $mapping->getId(),
            tagId: $tag->getId(),
            tagName: $tag->getName(),
            tagColor: $tag->getColor(),
            filesystemNodeUuid: $filesystemNode->getUuid(),
            filesystemNodeName: $filesystemNode->getName(),
        );
    }
}

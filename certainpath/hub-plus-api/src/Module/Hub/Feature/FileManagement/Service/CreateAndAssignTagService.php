<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Entity\FileSystemNodeTag;
use App\Entity\FileSystemNodeTagMapping;
use App\Module\Hub\Feature\FileManagement\DTO\Request\CreateAndAssignTagRequestDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\CreateAndAssignTagResponseDTO;
use App\Module\Hub\Feature\FileManagement\Exception\FilesystemNodeNotFoundException;
use App\Repository\FilesystemNodeRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateAndAssignTagService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FilesystemNodeRepository $filesystemNodeRepository,
    ) {
    }

    public function createAndAssignTag(CreateAndAssignTagRequestDTO $dto): CreateAndAssignTagResponseDTO
    {
        $filesystemNode = $this->filesystemNodeRepository->findOneByUuid($dto->filesystemNodeUuid);
        if (!$filesystemNode) {
            throw new FilesystemNodeNotFoundException();
        }

        $tag = new FileSystemNodeTag();
        $tag->setName($dto->name);
        if ($dto->color) {
            $tag->setColor($dto->color);
        }

        $this->em->persist($tag);

        $mapping = new FileSystemNodeTagMapping();
        $mapping->setFileSystemNodeTag($tag);
        $mapping->setFileSystemNode($filesystemNode);

        $this->em->persist($mapping);
        $this->em->flush();

        return new CreateAndAssignTagResponseDTO(
            id: $tag->getId(),
            name: $tag->getName(),
            color: $tag->getColor(),
            mappingId: $mapping->getId(),
            filesystemNodeUuid: $filesystemNode->getUuid(),
            createdAt: $tag->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $tag->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}

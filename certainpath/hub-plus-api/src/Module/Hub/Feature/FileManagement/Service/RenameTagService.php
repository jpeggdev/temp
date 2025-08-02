<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Module\Hub\Feature\FileManagement\DTO\Request\RenameTagRequestDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\RenameTagResponseDTO;
use App\Module\Hub\Feature\FileManagement\Exception\TagNotFoundException;
use App\Repository\FileSystemNodeTagRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class RenameTagService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FileSystemNodeTagRepository $tagRepository,
    ) {
    }

    public function renameTag(int $id, RenameTagRequestDTO $dto): RenameTagResponseDTO
    {
        $tag = $this->tagRepository->find($id);
        if (!$tag) {
            throw new TagNotFoundException();
        }

        $oldName = $tag->getName();
        $tag->setName($dto->name);

        if (null !== $dto->color) {
            $tag->setColor($dto->color);
        }

        $this->em->flush();

        return new RenameTagResponseDTO(
            id: $tag->getId(),
            oldName: $oldName,
            newName: $tag->getName(),
            color: $tag->getColor(),
            updatedAt: $tag->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Service\ResourceTag;

use App\DTO\Request\ResourceTag\CreateUpdateResourceTagDTO;
use App\DTO\Response\ResourceTag\CreateUpdateResourceTagResponseDTO;
use App\Entity\ResourceTag;
use App\Exception\CreateUpdateResourceTagException;
use App\Repository\ResourceTagRepository;

readonly class EditResourceTagService
{
    public function __construct(
        private ResourceTagRepository $resourceTagRepository,
    ) {
    }

    public function editTag(ResourceTag $tag, CreateUpdateResourceTagDTO $dto): CreateUpdateResourceTagResponseDTO
    {
        $existing = $this->resourceTagRepository->findOneByName($dto->name);
        if ($existing && $existing->getId() !== $tag->getId()) {
            throw new CreateUpdateResourceTagException(sprintf('A ResourceTag with the name "%s" already exists.', $dto->name));
        }

        $tag->setName($dto->name);
        $this->resourceTagRepository->save($tag, true);

        return new CreateUpdateResourceTagResponseDTO(
            id: $tag->getId(),
            name: $tag->getName()
        );
    }
}

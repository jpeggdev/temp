<?php

declare(strict_types=1);

namespace App\Service\ResourceTag;

use App\DTO\Request\ResourceTag\CreateUpdateResourceTagDTO;
use App\DTO\Response\ResourceTag\CreateUpdateResourceTagResponseDTO;
use App\Entity\ResourceTag;
use App\Exception\CreateUpdateResourceTagException;
use App\Repository\ResourceTagRepository;

readonly class CreateResourceTagService
{
    public function __construct(
        private ResourceTagRepository $resourceTagRepository,
    ) {
    }

    /**
     * @throws CreateUpdateResourceTagException
     */
    public function createTag(
        CreateUpdateResourceTagDTO $dto,
        bool $returnExisting = false,
    ): CreateUpdateResourceTagResponseDTO {
        $existing = $this->resourceTagRepository->findOneByName($dto->name);

        if ($existing) {
            if ($returnExisting) {
                return new CreateUpdateResourceTagResponseDTO(
                    id: $existing->getId(),
                    name: $existing->getName()
                );
            }
            throw new CreateUpdateResourceTagException(sprintf(' A ResourceTag with the name "%s" already exists.', $dto->name));
        }

        $tag = new ResourceTag();
        $tag->setName($dto->name);

        $this->resourceTagRepository->save($tag, true);

        return new CreateUpdateResourceTagResponseDTO(
            id: $tag->getId(),
            name: $tag->getName()
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Service\EventTag;

use App\DTO\Request\EventTag\CreateUpdateEventTagDTO;
use App\DTO\Response\EventTag\CreateUpdateEventTagResponseDTO;
use App\Entity\EventTag;
use App\Exception\EventTag\CreateUpdateEventTagException;
use App\Repository\EventTagRepository;

readonly class CreateEventTagService
{
    public function __construct(
        private EventTagRepository $eventTagRepository,
    ) {
    }

    public function createTag(
        CreateUpdateEventTagDTO $dto,
    ): CreateUpdateEventTagResponseDTO {
        $existing = $this->eventTagRepository->findOneByName($dto->name);

        if ($existing) {
            throw new CreateUpdateEventTagException(sprintf('An EventTag with the name "%s" already exists.', $dto->name));
        }

        $tag = new EventTag();
        $tag->setName($dto->name);

        $this->eventTagRepository->save($tag, true);

        return new CreateUpdateEventTagResponseDTO(
            id: $tag->getId(),
            name: $tag->getName(),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Service\EventType;

use App\DTO\Request\EventType\CreateEventTypeDTO;
use App\DTO\Response\EventType\CreateUpdateEventTypeResponseDTO;
use App\Entity\EventType;
use App\Exception\EventType\EventTypeCreateUpdateException;
use App\Repository\EventTypeRepository;

readonly class CreateEventTypeService
{
    public function __construct(
        private EventTypeRepository $eventTypeRepository,
    ) {
    }

    public function createEventType(
        CreateEventTypeDTO $dto,
    ): CreateUpdateEventTypeResponseDTO {
        if ($this->eventTypeRepository->findOneByName($dto->name)) {
            throw new EventTypeCreateUpdateException(sprintf('An EventType with name "%s" already exists.', $dto->name));
        }

        $eventType = new EventType();
        $eventType->setName($dto->name);
        $eventType->setDescription($dto->description);
        $eventType->setIsActive($dto->isActive);
        $this->eventTypeRepository->save($eventType, true);

        return new CreateUpdateEventTypeResponseDTO(
            $eventType->getId(),
            $eventType->getName(),
            $eventType->getDescription(),
            $eventType->isActive(),
        );
    }
}

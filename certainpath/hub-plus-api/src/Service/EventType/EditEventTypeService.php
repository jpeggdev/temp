<?php

declare(strict_types=1);

namespace App\Service\EventType;

use App\DTO\Request\EventType\EditEventTypeDTO;
use App\DTO\Response\EventType\CreateUpdateEventTypeResponseDTO;
use App\Entity\EventType;
use App\Exception\EventType\EventTypeCreateUpdateException;
use App\Repository\EventTypeRepository;

readonly class EditEventTypeService
{
    public function __construct(
        private EventTypeRepository $eventTypeRepository,
    ) {
    }

    public function editEventType(
        EventType $eventType,
        EditEventTypeDTO $dto,
    ): CreateUpdateEventTypeResponseDTO {
        $existing = $this->eventTypeRepository->findOneByName($dto->name);
        if ($existing && $existing->getId() !== $eventType->getId()) {
            throw new EventTypeCreateUpdateException(sprintf('An EventType with name "%s" already exists.', $dto->name));
        }

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

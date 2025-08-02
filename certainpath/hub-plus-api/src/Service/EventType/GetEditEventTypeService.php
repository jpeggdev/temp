<?php

declare(strict_types=1);

namespace App\Service\EventType;

use App\DTO\Response\EventType\GetEditEventTypeResponseDTO;
use App\Entity\EventType;

readonly class GetEditEventTypeService
{
    public function getEditEventTypeDetails(
        EventType $eventType,
    ): GetEditEventTypeResponseDTO {
        return new GetEditEventTypeResponseDTO(
            id: $eventType->getId(),
            name: $eventType->getName(),
            description: $eventType->getDescription(),
            isActive: $eventType->isActive(),
        );
    }
}

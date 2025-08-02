<?php

declare(strict_types=1);

namespace App\Service\EventCategory;

use App\DTO\Response\EventCategory\GetEditEventCategoryResponseDTO;
use App\Entity\EventCategory;

readonly class GetEditEventCategoryService
{
    public function getEditEventCategoryDetails(EventCategory $eventCategory): GetEditEventCategoryResponseDTO
    {
        return new GetEditEventCategoryResponseDTO(
            id: $eventCategory->getId(),
            name: $eventCategory->getName(),
            description: $eventCategory->getDescription(),
            isActive: $eventCategory->isActive(),
        );
    }
}

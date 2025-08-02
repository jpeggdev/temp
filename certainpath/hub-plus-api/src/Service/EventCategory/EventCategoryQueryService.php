<?php

declare(strict_types=1);

namespace App\Service\EventCategory;

use App\DTO\Request\EventCategory\EventCategoryQueryDTO;
use App\DTO\Response\EventCategory\EventCategoryListResponseDTO;
use App\Entity\EventCategory;
use App\Repository\EventCategoryRepository;

readonly class EventCategoryQueryService
{
    public function __construct(
        private EventCategoryRepository $eventCategoryRepository,
    ) {
    }

    public function getEventCategories(EventCategoryQueryDTO $queryDto): array
    {
        $eventCategories = $this->eventCategoryRepository->findEventCategoriesByQuery($queryDto);
        $totalCount = $this->eventCategoryRepository->getTotalCount($queryDto);

        $eventCategoryDtos = array_map(
            fn (EventCategory $eventCategory) => EventCategoryListResponseDTO::fromEntity($eventCategory),
            $eventCategories
        );

        return [
            'eventCategories' => $eventCategoryDtos,
            'totalCount' => $totalCount,
        ];
    }
}

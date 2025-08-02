<?php

declare(strict_types=1);

namespace App\Service\EventType;

use App\DTO\Request\EventType\GetEventTypesRequestDTO;
use App\DTO\Response\EventType\GetEventTypesResponseDTO;
use App\Repository\EventTypeRepository;

readonly class GetEventTypesService
{
    public function __construct(
        private EventTypeRepository $eventTypeRepository,
    ) {
    }

    /**
     * @return array{
     *     eventTypes: GetEventTypesResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getEventTypes(GetEventTypesRequestDTO $dto): array
    {
        $types = $this->eventTypeRepository->findEventTypesByQuery($dto);
        $totalCount = $this->eventTypeRepository->countEventTypesByQuery($dto);

        $dtos = array_map(
            fn ($type) => GetEventTypesResponseDTO::fromEntity($type),
            $types,
        );

        return [
            'eventTypes' => $dtos,
            'totalCount' => $totalCount,
        ];
    }
}

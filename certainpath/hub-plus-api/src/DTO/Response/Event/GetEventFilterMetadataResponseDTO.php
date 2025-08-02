<?php

declare(strict_types=1);

namespace App\DTO\Response\Event;

class GetEventFilterMetadataResponseDTO
{
    /**
     * @param array<array{id:int,name:string}> $eventTypes
     * @param array<array{id:int,name:string}> $eventCategories
     * @param array<array{id:int,name:string}> $employeeRoles
     * @param array<array{id:int,name:string}> $trades
     * @param array<array{id:int,name:string}> $eventTags
     */
    public function __construct(
        public array $eventTypes,
        public array $eventCategories,
        public array $employeeRoles,
        public array $trades,
        public array $eventTags,
    ) {
    }
}

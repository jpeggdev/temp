<?php

declare(strict_types=1);

namespace App\DTO\Response\Event;

class GetEventResponseDTO
{
    /**
     * @param array<int, array{id:int,name:string}> $tags
     * @param array<int, array{id:int,name:string}> $trades
     * @param array<int, array{id:int,name:string}> $roles
     * @param array<int, array{
     *   id: int,
     *   uuid: string,
     *   originalFileName: string|null,
     *   fileUrl: string|null,
     * }> $files
     */
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public string $eventCode,
        public string $eventName,
        public string $eventDescription,
        public float $eventPrice,
        public bool $isPublished,
        public bool $isVoucherEligible,
        public ?int $eventTypeId,
        public ?string $eventTypeName,
        public ?int $eventCategoryId,
        public ?string $eventCategoryName,
        public ?string $thumbnailUrl,
        public ?int $thumbnailFileId,
        public ?string $thumbnailFileUuid,
        public array $tags = [],
        public array $trades = [],
        public array $roles = [],
        public array $files = [],
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Response\Event;

class GetEventDetailsResponseDTO
{
    /**
     * @param array<mixed> $tags
     * @param array<mixed> $trades
     * @param array<mixed> $roles
     * @param array<mixed> $sessions
     * @param array<mixed> $files
     */
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public string $eventCode,
        public string $eventName,
        public string $eventDescription,
        public float $eventPrice,
        public bool $isPublished,
        public ?string $eventTypeName,
        public ?string $eventCategoryName,
        public ?string $thumbnailUrl,
        public int $viewCount,
        public ?string $createdAt,
        public ?string $updatedAt,
        public array $tags,
        public array $trades,
        public array $roles,
        public array $sessions,
        public array $files,
        public bool $isFavorited = false,
        public bool $isVoucherEligible = false,
    ) {
    }
}

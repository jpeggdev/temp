<?php

declare(strict_types=1);

namespace App\DTO\Response\Event;

class CreateUpdateEventResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public string $eventCode,
        public string $eventName,
        public ?string $thumbnailUrl,
        public bool $isPublished,
        public bool $isVoucherEligible,
    ) {
    }
}

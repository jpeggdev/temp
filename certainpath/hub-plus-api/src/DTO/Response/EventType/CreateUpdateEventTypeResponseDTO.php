<?php

declare(strict_types=1);

namespace App\DTO\Response\EventType;

class CreateUpdateEventTypeResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $description,
        public bool $isActive,
    ) {
    }
}

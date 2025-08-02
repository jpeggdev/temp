<?php

declare(strict_types=1);

namespace App\DTO\Response\EventTag;

class CreateUpdateEventTagResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $name,
    ) {
    }
}

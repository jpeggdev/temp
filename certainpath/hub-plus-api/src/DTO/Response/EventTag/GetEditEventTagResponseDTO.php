<?php

declare(strict_types=1);

namespace App\DTO\Response\EventTag;

class GetEditEventTagResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $name,
    ) {
    }
}

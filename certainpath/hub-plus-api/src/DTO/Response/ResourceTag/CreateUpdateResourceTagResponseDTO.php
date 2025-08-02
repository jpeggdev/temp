<?php

declare(strict_types=1);

namespace App\DTO\Response\ResourceTag;

class CreateUpdateResourceTagResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $name,
    ) {
    }
}

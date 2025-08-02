<?php

declare(strict_types=1);

namespace App\DTO\Response\ResourceTag;

class GetEditResourceTagResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $name,
    ) {
    }
}

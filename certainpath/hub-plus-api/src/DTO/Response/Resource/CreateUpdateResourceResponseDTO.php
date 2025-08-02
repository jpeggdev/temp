<?php

declare(strict_types=1);

namespace App\DTO\Response\Resource;

class CreateUpdateResourceResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public ?string $title,
        public ?string $contentUrl,
        public ?string $thumbnailUrl,
        public bool $isPublished,
    ) {
    }
}

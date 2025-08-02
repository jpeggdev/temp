<?php

declare(strict_types=1);

namespace App\DTO\Response\EventTag;

use App\Entity\EventTag;

class GetEventTagsResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public static function fromEntity(EventTag $tag): self
    {
        return new self(
            $tag->getId(),
            $tag->getName() ?? ''
        );
    }
}

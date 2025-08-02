<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\Tag;

class TagListResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?\DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
    ) {
    }

    public static function fromEntity(Tag $tag): self
    {
        return new self(
            $tag->getId(),
            $tag->getName(),
            $tag->getDescription(),
            $tag->getCreatedAt(),
            $tag->getUpdatedAt(),
        );
    }
}

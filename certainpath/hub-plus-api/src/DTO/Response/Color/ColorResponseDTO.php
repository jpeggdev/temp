<?php

declare(strict_types=1);

namespace App\DTO\Response\Color;

use App\Entity\Color;

class ColorResponseDTO
{
    public function __construct(
        public int $id,
        public string $value,
    ) {
    }

    public static function fromEntity(Color $color): self
    {
        return new self(
            id: $color->getId(),
            value: $color->getValue(),
        );
    }
}

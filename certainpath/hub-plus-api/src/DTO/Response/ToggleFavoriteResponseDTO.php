<?php

declare(strict_types=1);

namespace App\DTO\Response;

readonly class ToggleFavoriteResponseDTO
{
    public function __construct(
        public bool $isFavorited,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Response\Resource;

readonly class ValidateResourceSlugResponseDTO
{
    public function __construct(
        public bool $slugExists,
        public ?string $message = null,
    ) {
    }
}

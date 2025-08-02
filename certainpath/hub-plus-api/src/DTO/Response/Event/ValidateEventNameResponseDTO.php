<?php

declare(strict_types=1);

namespace App\DTO\Response\Event;

readonly class ValidateEventNameResponseDTO
{
    public function __construct(
        public bool $codeExists,
        public ?string $message = null,
    ) {
    }
}

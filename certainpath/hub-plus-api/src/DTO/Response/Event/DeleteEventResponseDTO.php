<?php

declare(strict_types=1);

namespace App\DTO\Response\Event;

class DeleteEventResponseDTO
{
    public function __construct(
        public int $id,
        public string $message,
    ) {
    }
}

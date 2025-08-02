<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response;

class DeleteEventSessionResponseDTO
{
    public function __construct(
        public int $id,
        public string $message,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Response;

final class UpdateEventInstructorResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $phone,
    ) {
    }
}

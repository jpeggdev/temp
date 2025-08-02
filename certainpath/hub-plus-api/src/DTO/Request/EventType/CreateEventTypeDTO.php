<?php

declare(strict_types=1);

namespace App\DTO\Request\EventType;

use Symfony\Component\Validator\Constraints as Assert;

class CreateEventTypeDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The name field cannot be empty')]
        public string $name,
        public ?string $description = null,
        #[Assert\NotNull]
        public bool $isActive = true,
    ) {
    }
}

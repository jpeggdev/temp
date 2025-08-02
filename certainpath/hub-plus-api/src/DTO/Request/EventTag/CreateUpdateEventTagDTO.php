<?php

declare(strict_types=1);

namespace App\DTO\Request\EventTag;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUpdateEventTagDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Event tag name cannot be empty')]
        public string $name,
    ) {
    }
}

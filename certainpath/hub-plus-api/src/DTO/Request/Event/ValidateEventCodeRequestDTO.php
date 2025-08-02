<?php

declare(strict_types=1);

namespace App\DTO\Request\Event;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ValidateEventCodeRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $eventCode,
        #[Assert\Length(max: 36)]
        public ?string $eventUuid = null,
    ) {
    }
}

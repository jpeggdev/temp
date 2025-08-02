<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\Shared\Timezone\DTO\Response;

use App\Entity\Timezone;

class GetTimezoneResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $shortName,
    ) {
    }

    public static function fromEntity(Timezone $timezone): self
    {
        return new self(
            id: $timezone->getId(),
            name: $timezone->getName(),
            shortName: $timezone->getShortName(),
        );
    }
}

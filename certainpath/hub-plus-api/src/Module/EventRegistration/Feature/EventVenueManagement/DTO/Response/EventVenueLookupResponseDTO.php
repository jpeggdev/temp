<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Response;

use App\Entity\EventVenue;

class EventVenueLookupResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public static function fromEntity(EventVenue $venue): self
    {
        return new self(
            id: $venue->getId(),
            name: $venue->getName(),
        );
    }
}

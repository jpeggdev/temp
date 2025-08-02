<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Response;

use App\Entity\EventVenue;

class EventVenueResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $address,
        public string $city,
        public string $state,
        public string $postalCode,
        public string $country,
        public bool $isActive,
        public ?string $description,
        public ?string $address2,
        public ?\DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
        public ?\DateTimeInterface $deletedAt,
    ) {
    }

    public static function fromEntity(EventVenue $eventVenue): self
    {
        return new self(
            id: $eventVenue->getId(),
            name: $eventVenue->getName(),
            address: $eventVenue->getAddress(),
            city: $eventVenue->getCity(),
            state: $eventVenue->getState(),
            postalCode: $eventVenue->getPostalCode(),
            country: $eventVenue->getCountry(),
            isActive: $eventVenue->isActive(),
            description: $eventVenue->getDescription(),
            address2: $eventVenue->getAddress2(),
            createdAt: $eventVenue->getCreatedAt(),
            updatedAt: $eventVenue->getUpdatedAt(),
            deletedAt: $eventVenue->getDeletedAt(),
        );
    }
}

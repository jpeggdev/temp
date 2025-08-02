<?php

declare(strict_types=1);

namespace App\DTO\Response\Unification\Location;

class LocationResponseDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?array $postalCodes = [],
        public ?bool $isActive = false,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            postalCodes: $data['postalCodes'] ?? [],
            isActive: $data['isActive'] ?? false,
        );
    }
}

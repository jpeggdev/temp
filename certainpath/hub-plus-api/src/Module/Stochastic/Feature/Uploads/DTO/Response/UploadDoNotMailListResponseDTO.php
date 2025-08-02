<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\Uploads\DTO\Response;

class UploadDoNotMailListResponseDTO
{
    public function __construct(
        public ?string $address1 = null,
        public ?string $address2 = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postalCode = null,
        public ?string $externalId = null,
        public bool $isMatched = false,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            address1: $data['address1'] ?? null,
            address2: $data['address2'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            postalCode: $data['zip'] ?? null,
            externalId: $data['externalId'] ?? null,
            isMatched: $data['isMatched'] ?? false,
        );
    }
}

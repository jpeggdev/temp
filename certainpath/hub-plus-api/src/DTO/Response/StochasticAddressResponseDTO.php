<?php

declare(strict_types=1);

namespace App\DTO\Response;

class StochasticAddressResponseDTO
{
    public function __construct(
        public int $id,
        public ?string $address1,
        public ?string $address2,
        public ?string $city,
        public ?string $stateCode,
        public ?string $postalCode,
        public bool $isBusiness,
        public bool $isVacant,
        public bool $isDoNotMail,
        public bool $isGlobalDoNotMail,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['address1'] ?? null,
            $data['address2'] ?? null,
            $data['city'] ?? null,
            $data['stateCode'] ?? null,
            $data['postalCode'] ?? null,
            (bool) ($data['isBusiness'] ?? false),
            (bool) ($data['isVacant'] ?? false),
            (bool) ($data['isDoNotMail'] ?? false),
            (bool) ($data['isGlobalDoNotMail'] ?? false),
        );
    }
}

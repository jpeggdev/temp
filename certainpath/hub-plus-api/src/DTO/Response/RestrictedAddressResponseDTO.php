<?php

namespace App\DTO\Response;

class RestrictedAddressResponseDTO
{
    public function __construct(
        public int $id,
        public ?string $address1,
        public ?string $address2,
        public ?string $city,
        public ?string $stateCode,
        public ?string $postalCode,
        public ?string $countryCode,
        public bool $isBusiness,
        public bool $isVacant,
        public bool $isVerified,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['address1'],
            $data['address2'] ?? null,
            $data['city'],
            $data['stateCode'],
            $data['postalCode'],
            $data['countryCode'],
            (bool) ($data['isBusiness'] ?? false),
            (bool) ($data['isVacant'] ?? false),
            (bool) ($data['isVerified'] ?? false),
            new \DateTimeImmutable($data['createdAt']),
            new \DateTimeImmutable($data['updatedAt']),
        );
    }
}

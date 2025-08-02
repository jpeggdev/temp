<?php

declare(strict_types=1);

namespace App\DTO\Response;

class StochasticProspectResponseDTO
{
    public function __construct(
        public int $id,
        public string $fullName,
        public ?string $firstName,
        public ?string $lastName,
        public ?bool $isPreferred,
        public ?bool $doNotMail,
        public ?bool $doNotContact,
        public string $companyName,
        public ?\DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
        public ?StochasticAddressResponseDTO $address,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $address = null;
        if (isset($data['address']) && is_array($data['address'])) {
            $address = StochasticAddressResponseDTO::fromArray($data['address']);
        }

        return new self(
            $data['id'],
            $data['fullName'],
            $data['firstName'] ?? null,
            $data['lastName'] ?? null,
            $data['isPreferred'] ?? null,
            $data['doNotMail'] ?? null,
            $data['doNotContact'] ?? null,
            $data['companyName'],
            isset($data['createdAt']) ? new \DateTimeImmutable($data['createdAt']) : null,
            isset($data['updatedAt']) ? new \DateTimeImmutable($data['updatedAt']) : null,
            $address
        );
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\Prospect;
use App\Entity\Address;

class ProspectListResponseDTO
{
    public function __construct(
        public int $id,
        public string $fullName,
        public ?string $firstName,
        public ?string $lastName,
        public ?bool $isPreferred,
        public ?bool $doNotMail,
        public ?bool $doNotContact,
        public ?string $companyName,
        public ?\DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
        public ?AddressResponseDTO $address,
    ) {
    }

    public static function fromEntity(Prospect $prospect): self
    {
        $addr = $prospect->getPreferredAddress();
        if (!$addr) {
            $addr = $prospect->getMostRecentValidAddress();
        }

        $addressDto = null;
        if ($addr instanceof Address) {
            $addressDto = AddressResponseDTO::fromEntity($addr);
        }

        return new self(
            $prospect->getId(),
            $prospect->getFullName(),
            $prospect->getFirstName(),
            $prospect->getLastName(),
            $prospect->isPreferred(),
            $addressDto?->isDoNotMail ?? $addressDto?->isGlobalDoNotMail ?? $prospect->isDoNotMail(),
            $prospect->isDoNotContact(),
            $prospect->getCompany()?->getName(),
            $prospect->getCreatedAt(),
            $prospect->getUpdatedAt(),
            $addressDto
        );
    }
}

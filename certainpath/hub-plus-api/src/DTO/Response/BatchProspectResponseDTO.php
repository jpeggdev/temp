<?php

declare(strict_types=1);

namespace App\DTO\Response;

class BatchProspectResponseDTO
{
    public function __construct(
        public int $id,
        public ?string $fullName = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?bool $doNotMail = null,
        public ?bool $doNotContact = null,
        public ?bool $isPreferred = null,
        public ?bool $isActive = null,
        public ?bool $isDeleted = null,
        public ?string $address1 = null,
        public ?string $address2 = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postalCode = null,
        public ?string $externalId = null,
        public ?int $companyId = null,
        public ?int $customerId = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['full_name'],
            $data['first_name'],
            $data['last_name'],
            $data['do_not_mail'] ?? false,
            $data['do_not_contact'] ?? false,
            $data['is_preferred'] ?? false,
            $data['is_active'] ?? false,
            $data['is_deleted'] ?? false,
            $data['address1'] ?? null,
            $data['address2'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['postal_code'] ?? null,
            $data['external_id'] ?? null,
            $data['company_id'] ?? null,
            $data['customer_id'] ?? null
        );
    }
}

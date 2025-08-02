<?php

declare(strict_types=1);

namespace App\DTO;

class IdentityQueryDTO
{
    public function __construct(
        public string $id,
        public string $email,
        public ?string $firstName,
        public ?string $lastName,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['user_id'] ?? '',
            email: $data['email'] ?? '',
            firstName: $data['given_name'] ?? null,
            lastName: $data['family_name'] ?? null
        );
    }
}

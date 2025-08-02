<?php

declare(strict_types=1);

namespace App\DTO;

use Psr\Http\Message\ResponseInterface;

readonly class IdentityCreationDTO
{
    public function __construct(
        public string $id,
        public string $email,
        public string $firstName,
        public string $lastName,
    ) {
    }

    public static function fromResponse(ResponseInterface $response): self
    {
        $data = json_decode($response->getBody()->getContents(), true);

        return new self(
            id: $data['user_id'] ?? '',
            email: $data['email'] ?? '',
            firstName: $data['given_name'] ?? '',
            lastName: $data['family_name'] ?? ''
        );
    }
}

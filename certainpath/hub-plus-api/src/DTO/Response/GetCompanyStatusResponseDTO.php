<?php

namespace App\DTO\Response;

class GetCompanyStatusResponseDTO
{
    public function __construct(
        public array $statusKeyValues = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['statusKeyValues'] ?? [],
        );
    }
}

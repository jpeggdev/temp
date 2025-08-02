<?php

namespace App\DTO\Response;

readonly class GetCompanyProspectsAggregatedResponseDTO
{
    public function __construct(
        public string $postalCode,
        public int $households,
        public float $avgSales,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['postalCode'] ?? '',
            (int) ($data['households'] ?? 0),
            (float) ($data['avgSales'] ?? 0),
        );
    }
}

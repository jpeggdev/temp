<?php

declare(strict_types=1);

namespace App\DTO\Response;

class CampaignPricingResponseDTO
{
    public function __construct(
        public float $postageExpense = 0.00,
        public float $materialExpense = 0.00,
        public float $totalExpense = 0.00,
        public int $actualQuantity = 0,
        public int $projectedQuantity = 0,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\BatchPostage;

class BatchPricingResponseDTO
{
    public function __construct(
        public int $batchPostageId,
        public string $reference,
        public ?float $postageExpense,
        public ?float $materialExpense,
        public ?float $totalExpense,
        public ?float $pricePerPiece,
        public int $actualQuantity = 0,
        public int $projectedQuantity = 0,
        public bool $canBeBilled = false,
    ) {
    }

    public static function fromEntity(BatchPostage $postage): self
    {
        return new self(
            $postage->getId(),
            $postage->getReference(),
            round((float) $postage->getCost(), 2),
            null,
            null,
            null,
            $postage->getQuantitySent(),
        );
    }
}

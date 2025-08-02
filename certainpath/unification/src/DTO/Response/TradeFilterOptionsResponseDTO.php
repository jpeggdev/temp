<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\Trade;

class TradeFilterOptionsResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public static function fromEntity(Trade $trade): self
    {
        return new self(
            $trade->getId(),
            $trade->getName()
        );
    }
}

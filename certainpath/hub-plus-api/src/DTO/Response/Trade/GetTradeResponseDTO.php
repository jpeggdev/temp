<?php

declare(strict_types=1);

namespace App\DTO\Response\Trade;

use App\Entity\Trade;

class GetTradeResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $icon,
    ) {
    }

    public static function fromEntity(Trade $trade): self
    {
        return new self(
            $trade->getId(),
            $trade->getName(),
            $trade->getIcon(),
        );
    }
}

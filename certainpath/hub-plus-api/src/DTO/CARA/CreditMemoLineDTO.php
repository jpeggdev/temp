<?php

namespace App\DTO\CARA;

use App\Entity\CreditMemoLineItem;

class CreditMemoLineDTO
{
    public string $description;
    public float $amount;
    public string $voucherCode;
    public string $uuid;

    public function __construct(
        string $description,
        float $amount,
        string $voucherCode,
        string $uuid,
    ) {
        $this->description = $description;
        $this->amount = $amount;
        $this->voucherCode = $voucherCode;
        $this->uuid = $uuid;
    }

    public static function fromEntity(CreditMemoLineItem $entity): self
    {
        return new self(
            $entity->getDescription(),
            (float) $entity->getAmount(),
            $entity->getVoucherCode() ?? '',
            $entity->getUuid()
        );
    }
}

<?php

namespace App\DTO\CARA;

class InvoiceLineItemDTO
{
    public \DateTimeImmutable $createdAt;

    public function __construct(
        public string $description,
        public int $quantity,
        public float $unitPrice,
        public float $lineTotal,
        string|\DateTimeImmutable $createdAt,
        public string $uuid,
        public ?string $discountCode,
        public ?string $discountSku,
    ) {
        if (is_string($createdAt)) {
            try {
                $createdAt = new \DateTimeImmutable($createdAt);
            } catch (\Exception) {
                $createdAt = new \DateTimeImmutable();
            }
        }
        $this->createdAt = $createdAt;
    }

    public static function fromEntity(\App\Entity\InvoiceLineItem $entity): self
    {
        return new self(
            $entity->getDescription(),
            $entity->getQuantity(),
            (float) $entity->getUnitPrice(),
            (float) $entity->getLineTotal(),
            $entity->getCreatedAt(),
            $entity->getUuid(),
            $entity->getDiscountCode(),
            null
        );
    }
}

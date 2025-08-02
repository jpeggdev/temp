<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Response;

use App\Entity\EventDiscount;

class GetEventDiscountResponseDTO extends BaseEventDiscountResponseDTO
{
    public function __construct(
        public int $id,
        public string $code,
        public array $discountType,
        public string $discountValue,
        public bool $isActive,
        public string $usage,
        public ?string $description,
        public ?int $maximumUses,
        public ?string $minimumPurchaseAmount,
        public ?array $events,
        public ?\DateTimeInterface $startDate,
        public ?\DateTimeInterface $endDate,
        public ?\DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
        public ?\DateTimeInterface $deletedAt,
    ) {
    }

    public static function fromEntity(
        EventDiscount $eventDiscount,
        string $usage,
    ): self {
        return new self(
            id: $eventDiscount->getId(),
            code: $eventDiscount->getCode(),
            discountType: self::prepareDiscountTypeData($eventDiscount),
            discountValue: $eventDiscount->getDiscountValue(),
            isActive: $eventDiscount->isActive(),
            usage: $usage,
            description: $eventDiscount->getDescription(),
            maximumUses: $eventDiscount->getMaximumUses(),
            minimumPurchaseAmount: $eventDiscount->getMinimumPurchaseAmount(),
            events: self::prepareEventsData($eventDiscount),
            startDate: $eventDiscount->getStartDate(),
            endDate: $eventDiscount->getEndDate(),
            createdAt: $eventDiscount->getCreatedAt(),
            updatedAt: $eventDiscount->getUpdatedAt(),
            deletedAt: $eventDiscount->getDeletedAt(),
        );
    }
}

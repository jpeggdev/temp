<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUpdateEventDiscountDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type(type: 'string', message: 'The code field must be a string.')]
        public string $code,
        #[Assert\NotNull(message: 'The discountTypeId cannot be null')]
        public int $discountTypeId,
        #[Assert\Type(type: 'string', message: 'The code field must be a string.')]
        public string $discountValue,
        #[Assert\Type(type: 'integer', message: 'The maximumUses must be a valid integer.')]
        #[Assert\Positive(message: 'The maximumUses must be a positive integer.')]
        public ?int $maximumUses = null,
        public ?string $minimumPurchaseAmount = null,
        public bool $isActive = true,
        #[Assert\Type(type: 'string', message: 'The code field must be a string.')]
        public ?string $description = null,
        public array $eventIds = [],
        public ?\DateTimeImmutable $startDate = null,
        public ?\DateTimeImmutable $endDate = null,
    ) {
    }
}

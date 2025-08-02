<?php

declare(strict_types=1);

namespace App\DTO\Request\Event;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUpdateEventDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The eventCode field cannot be empty')]
        public string $eventCode,
        #[Assert\NotBlank(message: 'The eventName field cannot be empty')]
        public string $eventName,
        #[Assert\NotBlank(message: 'The eventDescription field cannot be empty')]
        public string $eventDescription,
        #[Assert\NotNull(message: 'The eventPrice field cannot be null')]
        #[Assert\PositiveOrZero(message: 'eventPrice must be ≥ 0')]
        public float $eventPrice,
        #[Assert\NotNull]
        public bool $isPublished = false,
        public ?string $thumbnailUrl = null,
        // Keeping thumbnailFileId for backward compatibility
        public ?int $thumbnailFileId = null,
        // Adding new UUID field
        public ?string $thumbnailFileUuid = null,
        public ?int $eventCategoryId = null,
        public ?int $eventTypeId = null,
        /** @var int[] */
        #[Assert\Type('array')]
        public array $fileIds = [],
        /** @var string[] */
        #[Assert\Type('array')]
        public array $fileUuids = [],
        /** @var int[] */
        #[Assert\Type('array')]
        public array $tagIds = [],
        /** @var int[] */
        #[Assert\Type('array')]
        public array $tradeIds = [],
        /** @var int[] */
        #[Assert\Type('array')]
        public array $roleIds = [],
        public bool $isVoucherEligible = false,
    ) {
    }
}

<?php

namespace App\DTO\Query\BatchStatusStatusBulkEvent;

use Symfony\Component\Validator\Constraints as Assert;

class BulkBatchStatusEventsQueryDTO
{
    public const DEFAULT_SORT_ORDER = 'DESC';

    public function __construct(
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public ?int $year = null,
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public ?int $week = null,
        #[Assert\Type('string')]
        #[Assert\Choice(choices: ['ASC', 'DESC'])]
        public ?string $sortOrder = self::DEFAULT_SORT_ORDER,
    ) {
    }
}

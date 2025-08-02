<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class GetBulkBatchStatusQueryDTO
{
    public const string DEFAULT_SORT_ORDER = 'DESC';

    public function __construct(
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $year = 2025,
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $week = 1,
        #[Assert\Choice(choices: ['ASC', 'DESC'])]
        public string $sortOrder = self::DEFAULT_SORT_ORDER,
    ) {
    }
}

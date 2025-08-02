<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class StochasticClientMailDataQueryDTO
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_PER_PAGE = 10;
    public const DEFAULT_SORT_ORDER = 'DESC';

    public function __construct(
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $page = self::DEFAULT_PAGE,

        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $perPage = self::DEFAULT_PER_PAGE,

        #[Assert\Choice(choices: ['ASC', 'DESC'])]
        public string $sortOrder = self::DEFAULT_SORT_ORDER,

        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $year = 2025,

        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $week = 1,
    ) {
    }
}

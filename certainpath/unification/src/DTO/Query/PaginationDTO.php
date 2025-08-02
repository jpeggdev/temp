<?php

namespace App\DTO\Query;

use Symfony\Component\Validator\Constraints as Assert;

class PaginationDTO
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
        #[Assert\Type('array')]
        public ?array $includes = [],
        #[Assert\Type(type: 'string', message: 'The searchTerm field must be a string.')]
        public string $sortBy = 'id',
        #[Assert\Choice(choices: ['ASC', 'DESC'])]
        public string $sortOrder = self::DEFAULT_SORT_ORDER,
        #[Assert\Type(type: 'string', message: 'The searchTerm field must be a string.')]
        public ?string $searchTerm = null,
    ) {
    }
}

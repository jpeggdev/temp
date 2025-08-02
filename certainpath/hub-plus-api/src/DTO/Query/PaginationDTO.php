<?php

namespace App\DTO\Query;

use Symfony\Component\Validator\Constraints as Assert;

class PaginationDTO
{
    public const int DEFAULT_PAGE = 1;
    public const int DEFAULT_PAGE_SIZE = 10;
    public const string DEFAULT_SORT_BY = 'id';
    public const string DEFAULT_SORT_ORDER = 'DESC';

    public function __construct(
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $page = self::DEFAULT_PAGE,
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $pageSize = self::DEFAULT_PAGE_SIZE,
        #[Assert\Type(type: 'string', message: 'The sortBy field must be a string.')]
        public string $sortBy = self::DEFAULT_SORT_BY,
        #[Assert\Choice(choices: ['ASC', 'DESC'])]
        public string $sortOrder = self::DEFAULT_SORT_ORDER,
        #[Assert\Type(type: 'string', message: 'The searchTerm field must be a string.')]
        public ?string $searchTerm = null,
    ) {
    }
}

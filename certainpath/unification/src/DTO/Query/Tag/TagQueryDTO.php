<?php

declare(strict_types=1);

namespace App\DTO\Query\Tag;

use Symfony\Component\Validator\Constraints as Assert;

class TagQueryDTO
{
    public function __construct(
        public ?string $searchTerm = null,
        public ?string $companyIdentifier = null,
        public bool $systemTags = false,
        public ?int $page = 1,
        #[Assert\GreaterThanOrEqual(1, message: 'Page size must be at least 1')]
        public ?int $pageSize = 5,
        public ?string $sortBy = 'name',
        public ?string $sortOrder = 'asc'
    ) {
    }
}

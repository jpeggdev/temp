<?php

declare(strict_types=1);

namespace App\DTO\Request\Resource;

use Symfony\Component\Validator\Constraints as Assert;

class GetResourceSearchResultsQueryDTO
{
    public function __construct(
        public ?string $searchTerm = null,
        public ?int $page = 1,
        #[Assert\GreaterThanOrEqual(1, message: 'Page size must be at least 1')]
        public ?int $pageSize = 10,
        public ?string $sortBy = null,
        public ?string $sortOrder = 'asc',
        public ?array $tradeIds = null,
        public ?array $categoryIds = null,
        public ?array $employeeRoleIds = null,
        public ?array $tagIds = null,
        public ?array $resourceTypeIds = null,
        public bool $showFavorites = false,
    ) {
    }
}

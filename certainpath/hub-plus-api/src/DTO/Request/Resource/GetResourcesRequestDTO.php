<?php

declare(strict_types=1);

namespace App\DTO\Request\Resource;

use Symfony\Component\Validator\Constraints as Assert;

class GetResourcesRequestDTO
{
    public function __construct(
        public ?string $searchTerm = null,
        public ?int $page = 1,
        #[Assert\GreaterThanOrEqual(1, message: 'Page size must be at least 1')]
        public ?int $pageSize = 10,
        public ?string $sortBy = 'createdAt',
        public ?string $sortOrder = 'desc',
        public ?array $tradeIds = null,
        public ?array $categoryIds = null,
        public ?array $employeeRoleIds = null,
        public ?array $tagIds = null,
        public ?array $resourceTypeIds = null,
    ) {
    }
}

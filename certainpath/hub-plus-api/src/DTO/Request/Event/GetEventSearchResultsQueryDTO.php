<?php

declare(strict_types=1);

namespace App\DTO\Request\Event;

use Symfony\Component\Validator\Constraints as Assert;

class GetEventSearchResultsQueryDTO
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
        public ?array $eventTypeIds = null,
        public bool $showFavorites = false,
        public bool $onlyPastEvents = false,
        public ?string $startDate = null,
        public ?string $endDate = null,
    ) {
    }
}

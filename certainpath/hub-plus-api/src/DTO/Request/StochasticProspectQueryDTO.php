<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class StochasticProspectQueryDTO
{
    public function __construct(
        public ?string $searchTerm = null,
        public ?int $page = 1,
        #[Assert\GreaterThanOrEqual(1, message: 'Page size must be at least 1')]
        public ?int $pageSize = 10,
        public ?string $sortBy = 'createdAt',
        public ?string $sortOrder = 'asc',
    ) {
    }
}

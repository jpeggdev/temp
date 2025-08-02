<?php

declare(strict_types=1);

namespace App\DTO\Request\Customer;

use Symfony\Component\Validator\Constraints as Assert;

class CustomerQueryDTO
{
    public function __construct(
        public ?string $searchTerm = null,
        public ?int $page = 1,
        #[Assert\GreaterThanOrEqual(1, message: 'Page size must be at least 1')]
        public ?int $pageSize = 10,
        public ?string $sortBy = 'name',
        public ?string $sortOrder = 'asc',
        public ?string $intacctId = null,
        public ?bool $isActive = null,
    ) {
    }
}

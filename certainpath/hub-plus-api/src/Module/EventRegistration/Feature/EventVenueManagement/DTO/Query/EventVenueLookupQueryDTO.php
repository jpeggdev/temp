<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Query;

use Symfony\Component\Validator\Constraints as Assert;

class EventVenueLookupQueryDTO
{
    public function __construct(
        public ?bool $isActive = true,
        public ?int $page = 1,
        #[Assert\GreaterThanOrEqual(1)]
        public ?int $pageSize = 10,
        public ?string $sortBy = 'name',
        public ?string $sortOrder = 'asc',
        public ?string $searchTerm = null,
    ) {
    }
}

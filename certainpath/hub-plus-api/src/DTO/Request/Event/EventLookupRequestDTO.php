<?php

declare(strict_types=1);

namespace App\DTO\Request\Event;

use Symfony\Component\Validator\Constraints as Assert;

class EventLookupRequestDTO
{
    public function __construct(
        public ?string $searchTerm = null,
        public ?int $page = 1,
        #[Assert\GreaterThanOrEqual(1)]
        public ?int $pageSize = 10,
        public string $sortBy = 'eventName',
        public string $sortOrder = 'asc',
    ) {
    }
}

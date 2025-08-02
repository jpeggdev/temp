<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class EventSessionLookupRequestDTO
{
    public function __construct(
        public ?int $eventId = null,
        public ?bool $isPublished = true,
        public ?int $page = 1,
        #[Assert\GreaterThanOrEqual(1)]
        public ?int $pageSize = 10,
        public string $sortBy = 'startDate',
        public string $sortOrder = 'asc',
        public ?string $searchTerm = null,
    ) {
    }
}

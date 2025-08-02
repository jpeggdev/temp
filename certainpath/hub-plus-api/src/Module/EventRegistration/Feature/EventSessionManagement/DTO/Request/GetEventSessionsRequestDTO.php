<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class GetEventSessionsRequestDTO
{
    public function __construct(
        public ?string $eventUuid = null,
        public ?bool $isPublished = null,
        public ?int $page = 1,
        #[Assert\GreaterThanOrEqual(1)]
        public ?int $pageSize = 10,
        public string $sortBy = 'createdAt',
        public string $sortOrder = 'desc',
    ) {
    }
}

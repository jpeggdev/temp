<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class EventEnrollmentsQueryDTO
{
    public function __construct(
        #[Assert\Type('string')]
        public ?string $searchTerm = null,
        #[Assert\Choice(['ASC', 'DESC'])]
        public ?string $sortOrder = 'ASC',
        #[Assert\Type('string')]
        public ?string $sortBy = 'enrolledAt',
        #[Assert\GreaterThanOrEqual(1)]
        public int $page = 1,
        #[Assert\GreaterThanOrEqual(1)]
        public int $pageSize = 10,
    ) {
    }
}

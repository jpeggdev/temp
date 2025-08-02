<?php

declare(strict_types=1);

namespace App\DTO\Request\EventCategory;

use Symfony\Component\Validator\Constraints as Assert;

readonly class EventCategoryQueryDTO
{
    public function __construct(
        #[Assert\Type('string')]
        public ?string $searchTerm = null,
        #[Assert\Choice(['ASC', 'DESC'])]
        public ?string $sortOrder = null,
        public ?string $sortBy = null,
        #[Assert\Type('boolean')]
        public ?bool $isActive = null,
        #[Assert\GreaterThanOrEqual(1)]
        public int $page = 1,
        #[Assert\GreaterThanOrEqual(1)]
        public int $pageSize = 10,
    ) {
    }
}

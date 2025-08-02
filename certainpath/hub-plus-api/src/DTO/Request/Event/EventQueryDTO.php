<?php

declare(strict_types=1);

namespace App\DTO\Request\Event;

use Symfony\Component\Validator\Constraints as Assert;

readonly class EventQueryDTO
{
    public int $offset;
    public int $limit;

    public function __construct(
        public ?string $searchTerm = null,
        #[Assert\Range(min: 1)]
        public int $page = 1,
        #[Assert\Range(min: 1, max: 1000)]
        public int $pageSize = 10,
        public ?string $sortBy = null,
        #[Assert\Choice(['ASC', 'DESC'])]
        public ?string $sortOrder = null,
    ) {
        $this->offset = ($page - 1) * $pageSize;
        $this->limit = $pageSize;
    }
}

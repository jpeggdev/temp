<?php

declare(strict_types=1);

namespace App\DTO\Query\CampaignFile;

use Symfony\Component\Validator\Constraints as Assert;

class CampaignFileQueryDTO
{
    public function __construct(
        public ?string $searchTerm = null,
        public ?int $page = 1,
        #[Assert\GreaterThanOrEqual(1, message: 'Page size must be at least 1')]
        public ?int $pageSize = 10,
        public ?string $sortBy = 'originalFilename',
        #[Assert\Choice(choices: ['asc', 'desc'], message: 'Sort order must be either "asc" or "desc"')]
        public ?string $sortOrder = 'asc',
    ) {
    }
}

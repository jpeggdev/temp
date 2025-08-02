<?php

declare(strict_types=1);

namespace App\DTO\Query\EmailCampaignStatuses;

use Symfony\Component\Validator\Constraints as Assert;

class GetEmailCampaignStatusesDTO
{
    public function __construct(
        public ?string $searchTerm = null,
        #[Assert\Positive(message: 'The page must be a positive integer.')]
        #[Assert\Type(type: 'integer', message: 'The page must be a valid integer.')]
        public ?int $page = 1,
        #[Assert\GreaterThanOrEqual(1, message: 'Page size must be at least 1')]
        public ?int $pageSize = 10,
        public ?string $sortBy = 'id',
        #[Assert\Choice(['ASC', 'DESC'])]
        public ?string $sortOrder = 'ASC',
    ) {
    }
}

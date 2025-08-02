<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\DTO\Query;

use Symfony\Component\Validator\Constraints as Assert;

class GetEmailCampaignActivityLogsDTO extends BaseGetEmailCampaignActivityLogDTO
{
    public function __construct(
        #[Assert\Type(type: 'string', message: 'The search term must be a valid string.')]
        public ?string $searchTerm = null,
        #[Assert\Type(type: 'integer', message: 'The page must be a valid integer.')]
        #[Assert\Positive(message: 'The page must be a positive integer.')]
        public ?int $page = 1,
        #[Assert\GreaterThanOrEqual(1, message: 'Page size must be at least 1')]
        public ?int $pageSize = 10,
        #[Assert\Type(type: 'string', message: 'The sort by must be a valid string.')]
        public ?string $sortBy = 'id',
        #[Assert\Choice(['ASC', 'DESC'])]
        public ?string $sortOrder = 'ASC',
        public ?string $emailEventPeriodFilter = '',
    ) {
        parent::__construct($emailEventPeriodFilter);
    }
}

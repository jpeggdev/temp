<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Query;

use App\DTO\Query\PaginationDTO;
use Symfony\Component\Validator\Constraints as Assert;

class GetEventVenuesDTO extends PaginationDTO
{
    public function __construct(
        int $page = self::DEFAULT_PAGE,
        int $pageSize = self::DEFAULT_PAGE_SIZE,
        string $sortBy = self::DEFAULT_SORT_BY,
        string $sortOrder = self::DEFAULT_SORT_ORDER,
        #[Assert\Type(type: 'string', message: 'The sort by must be a valid string.')]
        public ?string $searchTerm = null,
        #[Assert\Type(type: 'bool', message: 'The isActive flag must be a valid boolean.')]
        public ?bool $isActive = true,
    ) {
        parent::__construct($page, $pageSize, $sortBy, $sortOrder);
    }
}

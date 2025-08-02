<?php

namespace App\DTO\Query\FilterOption;

use App\DTO\Query\NewPaginationDTO;
use Symfony\Component\Validator\Constraints as Assert;

class GetCityFilterOptionsDTO extends NewPaginationDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The intacctId field cannot be blank.')]
        #[Assert\Type(type: 'string', message: 'The intacctId field must be a string.')]
        public ?string $intacctId = '',

        int $page = self::DEFAULT_PAGE,
        int $pageSize = self::DEFAULT_PAGE_SIZE,
        string $sortBy = self::DEFAULT_SORT_BY,
        string $sortOrder = self::DEFAULT_SORT_ORDER,
        ?string $searchTerm = null,
    ) {
        parent::__construct($page, $pageSize, $sortBy, $sortOrder, $searchTerm);
    }
}

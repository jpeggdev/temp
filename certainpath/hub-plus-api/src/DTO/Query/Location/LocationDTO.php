<?php

declare(strict_types=1);

namespace App\DTO\Query\Location;

use Symfony\Component\Validator\Constraints as Assert;

class LocationDTO
{
    public function __construct(
        #[Assert\Type(type: 'string', message: 'The searchTerm field must be a string.')]
        public ?string $searchTerm = null,
        #[Assert\GreaterThanOrEqual(1, message: 'Page must be at least 1')]
        public ?int $page = 1,
        #[Assert\GreaterThanOrEqual(1, message: 'Page size must be at least 1')]
        public ?int $pageSize = 10,
        #[Assert\Choice(choices: ['id', 'name'], message: 'Sort order must be either "id" or "name"')]
        public ?string $sortBy = 'id',
        #[Assert\Choice(choices: ['ASC', 'DESC'], message: 'Sort order must be either "ASC" or "DESC"')]
        public ?string $sortOrder = 'ASC',
        public ?bool $isActive = null,
    ) {
    }
}

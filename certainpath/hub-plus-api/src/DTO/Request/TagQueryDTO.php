<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class TagQueryDTO
{
    public function __construct(
        public ?string $searchTerm = null,
        public ?string $companyIdentifier = null,
        public bool $systemTags = false,
        #[Assert\Choice(choices: ['ASC', 'DESC'])]
        public string $sortOrder = 'ASC',
        #[Assert\Choice(choices: ['id', 'name'])]
        public string $sortBy = 'name',
        #[Assert\Positive]
        public int $page = 1,
        #[Assert\Positive]
        public int $perPage = 5,
    ) {
    }
}

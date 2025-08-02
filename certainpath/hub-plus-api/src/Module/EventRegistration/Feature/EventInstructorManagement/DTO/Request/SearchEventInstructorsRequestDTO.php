<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class SearchEventInstructorsRequestDTO
{
    public function __construct(
        #[Assert\Length(max: 255)]
        public readonly ?string $searchTerm = null,
        #[Assert\GreaterThanOrEqual(1)]
        public readonly int $page = 1,
        #[Assert\GreaterThanOrEqual(1)]
        public readonly int $pageSize = 10,
        #[Assert\Choice(choices: ['id', 'name', 'email', 'phone'], message: 'Invalid sort field')]
        public string $sortBy = 'id',
        #[Assert\Choice(choices: ['ASC', 'DESC'], message: 'Invalid sort order')]
        public string $sortOrder = 'ASC',
    ) {
        $this->sortOrder = strtoupper($this->sortOrder);
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UserQueryDTO
{
    public function __construct(
        #[Assert\Length(max: 255)]
        public readonly ?string $firstName = null,
        #[Assert\Length(max: 255)]
        public readonly ?string $lastName = null,
        #[Assert\Length(max: 255)]
        public readonly ?string $email = null,
        #[Assert\Length(max: 255)]
        public readonly ?string $salesforceId = null,
        #[Assert\Length(max: 255)]
        public readonly ?string $searchTerm = null,
        #[Assert\GreaterThanOrEqual(1)]
        public readonly int $page = 1,
        #[Assert\GreaterThanOrEqual(1, message: 'Page size must be at least 1')]
        public readonly int $pageSize = 10,
        #[Assert\Choice(choices: ['id', 'firstName', 'lastName', 'email', 'salesforceId'], message: 'Invalid sort field')]
        public readonly ?string $sortBy = null,
        #[Assert\Choice(choices: ['ASC', 'DESC'], message: 'Invalid sort order')]
        public string $sortOrder = 'ASC',
    ) {
        $this->sortOrder = strtoupper($sortOrder);
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class GetMyCompaniesQueryDTO
{
    public function __construct(
        public int $page = 1,
        #[Assert\LessThanOrEqual(100)]
        public int $limit = 100,
        public ?string $search = null,
    ) {
    }
}

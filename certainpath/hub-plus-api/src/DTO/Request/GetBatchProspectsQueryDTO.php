<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class GetBatchProspectsQueryDTO
{
    public function __construct(
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $page = 1,
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $perPage = 10,
        #[Assert\Choice(choices: ['ASC', 'DESC'])]
        public string $sortOrder = 'DESC',
    ) {
    }
}

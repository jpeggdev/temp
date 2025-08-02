<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class FieldServiceExportQueryDTO
{
    public function __construct(
        #[Assert\Positive(message: 'Page number must be positive.')]
        public int $page = 1,
        #[Assert\Positive(message: 'Page size must be positive.')]
        #[Assert\LessThanOrEqual(100, message: 'Page size cannot exceed 100.')]
        public int $pageSize = 20,
        #[Assert\Choice(choices: ['ASC', 'DESC'], message: 'Invalid sort order')]
        public string $sortOrder = 'ASC',
    ) {
    }
}

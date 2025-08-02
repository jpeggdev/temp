<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class BulkUpdateBatchesStatusQueryDTO
{
    public function __construct(
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public ?int $year = null,
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public ?int $week = null,
        #[Assert\NotBlank]
        public ?string $status = null,
    ) {
    }
}

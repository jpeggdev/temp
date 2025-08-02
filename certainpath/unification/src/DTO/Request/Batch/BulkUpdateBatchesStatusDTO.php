<?php

namespace App\DTO\Request\Batch;

use Symfony\Component\Validator\Constraints as Assert;

class BulkUpdateBatchesStatusDTO
{
    public function __construct(
        #[Assert\Type('integer')]
        #[Assert\Positive]
        #[Assert\NotBlank]
        public ?int $year = null,
        #[Assert\Type('integer')]
        #[Assert\Positive]
        #[Assert\NotBlank]
        public ?int $week = null,
        #[Assert\NotBlank]
        public ?string $status = null,
    ) {
    }
}

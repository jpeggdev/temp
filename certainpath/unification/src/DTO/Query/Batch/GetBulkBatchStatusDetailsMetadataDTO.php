<?php

namespace App\DTO\Query\Batch;

use Symfony\Component\Validator\Constraints as Assert;

class GetBulkBatchStatusDetailsMetadataDTO
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
    ) {
    }
}

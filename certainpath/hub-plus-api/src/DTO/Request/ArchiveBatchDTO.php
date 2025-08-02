<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ArchiveBatchDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Batch ID should not be blank.')]
        #[Assert\Positive(message: 'Batch ID must be a positive integer.')]
        public int $batchId,
    ) {
    }
}

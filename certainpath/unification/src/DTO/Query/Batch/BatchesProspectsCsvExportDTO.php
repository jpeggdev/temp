<?php

namespace App\DTO\Query\Batch;

use Symfony\Component\Validator\Constraints as Assert;

readonly class BatchesProspectsCsvExportDTO
{
    public function __construct(
        #[Assert\NotNull(message: 'The week is required.')]
        #[Assert\NotBlank(message: 'The week cannot be blank.')]
        #[Assert\Positive(message: 'The week must be a positive integer.')]
        #[Assert\Type(type: 'integer', message: 'The week must be a valid integer.')]
        public ?int $week = null,

        #[Assert\NotNull(message: 'The year is required.')]
        #[Assert\NotBlank(message: 'The year cannot be blank.')]
        #[Assert\Positive(message: 'The year must be a positive integer.')]
        #[Assert\Type(type: 'integer', message: 'The year must be a valid integer.')]
        public ?int $year = null,
    ) {
    }
}

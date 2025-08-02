<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateEmployeeSessionEnrollmentDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Employee ID should not be blank.')]
        #[Assert\Positive(message: 'Employee ID must be a positive integer.')]
        #[Assert\Type(
            type: 'integer',
            message: 'The value {{ value }} is not a valid {{ type }}.'
        )]
        public readonly int $employeeId,
        #[Assert\NotBlank(message: 'Event session ID should not be blank.')]
        #[Assert\Positive(message: 'Event session ID must be a positive integer.')]
        #[Assert\Type(
            type: 'integer',
            message: 'The value {{ value }} is not a valid {{ type }}.'
        )]
        public readonly int $eventSessionId,
        #[Assert\Type(
            type: 'array',
            message: 'The value {{ value }} is not a valid {{ type }}.'
        )]
        public readonly ?array $employeeIds = null,
    ) {
    }
}

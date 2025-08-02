<?php

namespace App\DTO\Query\Prospect;

use Symfony\Component\Validator\Constraints as Assert;

class AggregatedProspectsDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The intacctId field cannot be blank.')]
        #[Assert\Type(type: 'string', message: 'The intacctId field must be a string.')]
        public ?string $intacctId = '',
    ) {
    }
}

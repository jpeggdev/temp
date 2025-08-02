<?php

namespace App\DTO\Query\Chart\Archived;

use Symfony\Component\Validator\Constraints as Assert;

class TotalSalesByYearAndMonthChartDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The intacctId field cannot be blank.')]
        #[Assert\Type(type: 'string', message: 'The intacctId field must be a string.')]
        public ?string $intacctId = '',
    ) {
    }
}

<?php

namespace App\DTO\Query\Chart;

use Symfony\Component\Validator\Constraints as Assert;

class StochasticDashboardDTO
{
    public const SCOPE_SALES = 'sales';
    public const SCOPE_CUSTOMERS = 'customers';

    public function __construct(
        #[Assert\NotBlank(message: 'The scope field cannot be blank.')]
        #[Assert\Type(type: 'string', message: 'The scope field must be a string.')]
        public ?string $scope = self::SCOPE_SALES,

        #[Assert\NotBlank(message: 'The intacctId field cannot be blank.')]
        #[Assert\Type(type: 'string', message: 'The intacctId field must be a string.')]
        public ?string $intacctId = '',

        #[Assert\Type(type: 'array', message: 'The trades field must be an array.')]
        #[Assert\All([
            new Assert\Type(
                type: 'numeric',
                message: 'The trades field must be an array of numbers.'
            )
        ])]
        public ?array $trades = [],

        #[Assert\Type(type: 'array', message: 'The years field must be an array.')]
        #[Assert\All([
            new Assert\Type(
                type: 'numeric',
                message: 'The years field must be an array of numbers.'
            )
        ])]
        public ?array $years = [],

        #[Assert\Type(type: 'array', message: 'The cities field must be an array.')]
        #[Assert\All([
            new Assert\Type(
                type: 'string',
                message: 'The cities field must be an array of strings.'
            )
        ])]
        public ?array $cities = [],
    ) {
    }
}

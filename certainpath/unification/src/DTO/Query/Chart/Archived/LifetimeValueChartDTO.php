<?php

namespace App\DTO\Query\Chart\Archived;

use Symfony\Component\Validator\Constraints as Assert;

class LifetimeValueChartDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The intacctId field cannot be blank.')]
        #[Assert\Type(type: 'string', message: 'The intacctId field must be a string.')]
        public ?string $intacctId = '',

        #[Assert\Type(type: 'array', message: 'The trades field must be an array.')]
        #[Assert\All([
            new Assert\Type(
                type: 'string',
                message: 'The trades field must be an array of strings.'
            )
        ])]
        public ?array $trades = [],

        #[Assert\Type(type: 'array', message: 'The cities field must be an array.')]
        #[Assert\All([
            new Assert\Type(
                type: 'string',
                message: 'The cities field must be an array of strings.'
            )
        ])]
        public ?array $cities = [],

        #[Assert\Type(type: 'array', message: 'The years field must be an array.')]
        #[Assert\All([
            new Assert\Type(
                type: 'numeric',
                message: 'The years field must be an array of numbers.'
            )
        ])]
        public ?array $years = [],
    ) {
    }
}
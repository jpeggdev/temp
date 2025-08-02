<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\DTO\Query;

use Symfony\Component\Validator\Constraints as Assert;

class GetLifetimeValueChartDTO
{
    public function __construct(
        #[Assert\Type(type: 'array', message: 'The trades field must be an array.')]
        #[Assert\All([
            new Assert\Type(
                type: 'string',
                message: 'The trades field must be an array of strings.'
            ),
        ])]
        public ?array $trades = [],
        #[Assert\Type(type: 'array', message: 'The cities field must be an array.')]
        #[Assert\All([
            new Assert\Type(
                type: 'string',
                message: 'The cities field must be an array of strings.'
            ),
        ])]
        public ?array $cities = [],
        #[Assert\Type(type: 'array', message: 'The years field must be an array.')]
        #[Assert\All([
            new Assert\Type(
                type: 'numeric',
                message: 'The years field must be an array of numbers.'
            ),
        ])]
        public ?array $years = [],
    ) {
    }
}

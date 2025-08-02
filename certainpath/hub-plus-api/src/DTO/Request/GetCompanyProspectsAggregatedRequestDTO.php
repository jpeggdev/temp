<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class GetCompanyProspectsAggregatedRequestDTO
{
    public function __construct(
        #[Assert\Type('string')]
        public string $customerInclusionRule = 'include_prospects_only',
        #[Assert\Type('string')]
        public string $lifetimeValueRule = '',
        #[Assert\Type('string')]
        public string $clubMembersRule = '',
        #[Assert\Type('string')]
        public string $installationsRule = '',
        #[Assert\Type('int')]
        public ?int $prospectMinAgeRule = null,
        #[Assert\Type('int')]
        public ?int $prospectMaxAgeRule = null,
        #[Assert\Type('int')]
        public ?int $minEstimatedIncomeRule = null,
        #[Assert\Type('int')]
        public ?int $minHomeAgeRule = null,
        #[Assert\Type('string')]
        public string $tagsRule = '',
        #[Assert\Type('array')]
        public ?array $locations = [],
        #[Assert\Type('string')]
        public string $addressTypeRule = 'residential',
    ) {
    }
}

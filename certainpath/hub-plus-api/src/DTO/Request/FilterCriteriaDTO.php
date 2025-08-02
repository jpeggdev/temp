<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Matches the nested "filterCriteria" structure:
 * {
 *   "prospectAge": { "min": "40", "max": "90" },
 *   "prospectIncome": "50000",
 *   "homeAge": "5",
 *   "excludeClubMembers": false,
 *   "excludeLTV": false,
 *   "excludeInstallCustomers": false,
 *   "excludeKnownCustomers": false
 * }
 */
class FilterCriteriaDTO
{
    public function __construct(
        #[Assert\NotNull(message: 'prospectAge is required')]
        #[Assert\Valid]
        public ProspectAgeDTO $prospectAge,
        #[Assert\NotBlank(message: 'The audience field cannot be empty')]
        public string $audience,
        #[Assert\Type('string', message: 'The addressType field cannot be empty')]
        public ?string $addressType = null,
        #[Assert\Type('string', message: 'prospectIncome must be a string')]
        public ?string $estimatedIncome = null,
        #[Assert\Type('string', message: 'homeAge must be a string')]
        public ?string $homeAge = null,
        #[Assert\Type('bool', message: 'excludeClubMembers must be boolean')]
        public bool $excludeClubMembers = false,
        #[Assert\Type('bool', message: 'excludeLTV must be boolean')]
        public bool $excludeLTV = false,
        #[Assert\Type('bool', message: 'excludeInstallCustomers must be boolean')]
        public bool $excludeInstallCustomers = false,
    ) {
    }
}

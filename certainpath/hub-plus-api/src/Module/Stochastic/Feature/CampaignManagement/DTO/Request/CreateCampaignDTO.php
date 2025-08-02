<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\DTO\Request;

use App\DTO\Request\FilterCriteriaDTO;
use App\DTO\Request\ZipCodeDTO;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The main DTO matching your posted JSON structure.
 *
 * {
 *   "campaignName": "Test Campaign",
 *   "campaignProduct": 103,
 *   "audience": "include_prospects_only",
 *   "description": "Test Description",
 *   "phoneNumber": "612-999-3340",
 *   "startDate": "2025-01-06",
 *   "endDate": "2025-01-31",
 *   "mailingFrequency": "6",
 *   "selectedMailingWeeks": [0, 2],
 *   "filterCriteria": {
 *       "prospectAge": { "min": "40", "max": "90" },
 *       "prospectIncome": "50000",
 *       "homeAge": "5",
 *       "excludeClubMembers": false,
 *       "excludeLTV": false,
 *       "excludeInstallCustomers": false,
 *       "excludeKnownCustomers": false
 *   },
 *   "zipCodes": [
 *       {
 *           "code": "37748",
 *           "avgSale": 0,
 *           "availableProspects": 2213,
 *           "selectedProspects": "2213",
 *           "filteredProspects": 2213
 *       },
 *       ...
 *   ],
 * "tags": "tag1,tag2,tag3"
 * }
 */
class CreateCampaignDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The campaignName field cannot be empty')]
        public string $campaignName,
        public ?int $campaignProduct,
        #[Assert\NotBlank(message: 'The startDate field cannot be empty')]
        #[Assert\DateTime(
            format: 'Y-m-d',
            message: 'The start date must be in the format YYYY-MM-DD.'
        )]
        public string $startDate,
        #[Assert\NotBlank(message: 'The endDate field cannot be empty')]
        #[Assert\DateTime(
            format: 'Y-m-d',
            message: 'The end date must be in the format YYYY-MM-DD.'
        )]
        public string $endDate,
        #[Assert\NotBlank(message: 'The mailingFrequency field cannot be empty')]
        #[Assert\Positive(message: 'The mailing frequency must be a positive integer')]
        public int $mailingFrequency,
        #[Assert\NotNull(message: 'filterCriteria is required')]
        #[Assert\Valid] // ensures nested DTO is validated
        public FilterCriteriaDTO $filterCriteria,
        /**
         * @var ZipCodeDTO[]
         */
        #[Assert\Type('array', message: 'zipCodes must be an array')]
        #[Assert\NotNull(message: 'zipCodes is required')]
        #[Assert\Valid]
        public array $zipCodes = [],
        public ?string $tags = null,
        #[Assert\Type('array', message: 'locations must be an array')]
        public array $locations = [],
        public ?string $description = null,
        public ?string $phoneNumber = null,
        #[Assert\Type('array', message: 'selectedMailingWeeks must be an array of integers')]
        #[Assert\Count(min: 1, minMessage: 'At least one mailing drop week is required.')]
        public array $selectedMailingWeeks = [],
    ) {
    }
}

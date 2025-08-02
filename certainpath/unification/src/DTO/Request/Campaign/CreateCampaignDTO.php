<?php

namespace App\DTO\Request\Campaign;

use App\DTO\Domain\ProspectFilterRulesDTO;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCampaignDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The name field cannot be empty')]
        #[Assert\Length(max: 255, maxMessage: 'The name field cannot be longer than {{ limit }} characters.')]
        public string $name,
        #[Assert\Type(type: 'integer')]
        public ?int $hubPlusProductId,
        #[Assert\NotBlank(message: 'The start date field cannot be empty')]
        #[Assert\DateTime(format: 'Y-m-d', message: 'The start date must be in the format Y-m-d.')]
        public string $startDate,
        #[Assert\NotBlank(message: 'The end date field cannot be empty')]
        #[Assert\DateTime(format: 'Y-m-d', message: 'The end date must be in the format Y-m-d.')]
        public string $endDate,
        #[Assert\NotBlank(message: 'The mailing frequency weeks field cannot be empty')]
        #[Assert\Positive(message:'The mailing frequency weeks must be a positive integer')]
        public int $mailingFrequencyWeeks,
        #[Assert\NotBlank(message: 'The company identifier field cannot be empty')]
        #[Assert\Length(max: 255, maxMessage: 'The company identifier field cannot be longer than {{ limit }} characters.')]
        public string $companyIdentifier,
        #[Assert\NotBlank(message: 'The mail package name field cannot be empty')]
        #[Assert\Length(max: 255, maxMessage: 'The mail package name field cannot be longer than {{ limit }} characters.')]
        public ?string $mailPackageName,
        #[Assert\Type(type: 'string', message: 'The description field must be a string.')]
        public ?string $description = null,
        public ?string $phoneNumber = null,
        #[Assert\Type(type: 'array')]
        #[Assert\Count(min: 1, minMessage: 'At least one mailing drop week is required.')]
        public array $mailingDropWeeks = [],
        #[Assert\Type(type: 'array')]
        public array $locationIds = [],
        #[MapRequestPayload]
        public ProspectFilterRulesDTO $prospectFilterRules = new ProspectFilterRulesDTO(),
    ) {
    }

    public function getIdentifier(): string
    {
        $bits = $this->companyIdentifier . '|';
        $bits .= $this->name . '|';
        $bits .= $this->mailPackageName . '|';
        $bits .= $this->startDate . '|';
        $bits .= $this->endDate . '|';

        if ($this->mailingDropWeeks) {
            $bits .= implode('-', $this->mailingDropWeeks) . '|';
        }

        if ($this->prospectFilterRules->intacctId) {
            $bits .= $this->prospectFilterRules->intacctId . '|';
        }

        if ($this->prospectFilterRules->customerInclusionRule) {
            $bits .= $this->prospectFilterRules->customerInclusionRule . '|';
        }

        if ($this->prospectFilterRules->lifetimeValueRule) {
            $bits .= $this->prospectFilterRules->lifetimeValueRule . '|';
        }

        if ($this->prospectFilterRules->clubMembersRule) {
            $bits .= $this->prospectFilterRules->clubMembersRule . '|';
        }

        if ($this->prospectFilterRules->installationsRule) {
            $bits .= $this->prospectFilterRules->installationsRule . '|';
        }

        if ($this->prospectFilterRules->prospectMinAge) {
            $bits .= $this->prospectFilterRules->prospectMinAge . '|';
        }

        if ($this->prospectFilterRules->prospectMaxAge) {
            $bits .= $this->prospectFilterRules->prospectMaxAge . '|';
        }

        if ($this->prospectFilterRules->minHomeAge) {
            $bits .= $this->prospectFilterRules->minHomeAge . '|';
        }

        if ($this->prospectFilterRules->minEstimatedIncome) {
            $bits .= $this->prospectFilterRules->minEstimatedIncome . '|';
        }

        if ($this->prospectFilterRules->postalCodes) {
            foreach ($this->prospectFilterRules->postalCodes as $postalCode => $limit) {
                $bits .= $postalCode . $limit;
            }
        }

        if ($this->prospectFilterRules->tags) {
            foreach ($this->prospectFilterRules->tags as $tag) {
                $bits .= $tag;
            }
        }

        return substr(hash('sha256', $bits), 0, 20);
    }
}

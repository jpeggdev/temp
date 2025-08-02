<?php

namespace App\DTO\Response;

class GetCampaignDetailsMetadataResponse
{
    public function __construct(
        public array $customerRestrictionCriteria,
        public array $addressTypes,
        public array $mailingFrequencies,
        public array $campaignTargets,
        public array $estimatedIncomeOptions,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['customerRestrictionCriteria'] ?? [],
            $data['addressTypes'] ?? [],
            $data['mailingFrequencies'] ?? [],
            $data['campaignTargets'] ?? [],
            $data['estimatedIncomeOptions'] ?? []
        );
    }
}

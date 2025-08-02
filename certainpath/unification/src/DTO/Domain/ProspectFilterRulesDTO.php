<?php

namespace App\DTO\Domain;

use App\Entity\Campaign;
use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;
use Symfony\Component\Validator\Constraints as Assert;

class ProspectFilterRulesDTO
{
    public function __construct(
        #[Assert\Type('string')]
        public string $intacctId = '',

        #[Assert\Type('string')]
        public string $customerInclusionRule = ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_ONLY_VALUE,

        #[Assert\Type('int')]
        public ?int $lifetimeValueRule = null,

        #[Assert\Type('string')]
        public string $clubMembersRule = '',

        #[Assert\Type('string')]
        public string $installationsRule = '',

        #[Assert\Type('int')]
        public ?int $prospectMinAge = null,

        #[Assert\Type('int')]
        public ?int $prospectMaxAge = null,

        #[Assert\Type('int')]
        public ?int $minHomeAge = null,

        #[Assert\Type('int')]
        public ?int $minEstimatedIncome = null,

        #[Assert\Type('array')]
        public array $postalCodes = [],

        #[Assert\Type('array')]
        public array $tags = [],

        #[Assert\Type('array')]
        public array $locations = [],

        #[Assert\Type('array')]
        public array $locationPostalCodes = [],

        #[Assert\Type('string')]
        public string $addressTypeRule = ProspectFilterRuleRegistry::INCLUDE_RESIDENTIAL_ONLY_RULE_VALUE,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public static function createFromCampaignObject(Campaign $campaign): self
    {
        $intacctId = $campaign->getCompany()?->getIdentifier();
        $customerInclusionRule = '';
        $customerMaxLTVRule = null;
        $clubMembersInclusionRule = '';
        $installationsInclusionRule = '';
        $prospectMinAgeRule = null;
        $prospectMaxAgeRule = null;
        $minHomeAgeRule = null;
        $minEstimatedIncome = null;
        $postalCodes = [];
        $tags = [];
        $addressTypeInclusionRule = '';

        foreach ($campaign->getProspectFilterRules() as $rule) {
            if ($rule->isCustomerInclusionRule()) {
                $customerInclusionRule = $rule->getValue();
                continue;
            }

            if ($rule->isCustomerMaxLtvRule()) {
                $customerMaxLTVRule = (int) $rule->getValue();
                continue;
            }

            if ($rule->isClubMembersInclusionRule()) {
                $clubMembersInclusionRule = $rule->getValue();
                continue;
            }

            if ($rule->isCustomerInstallationsInclusionRule()) {
                $installationsInclusionRule = $rule->getValue();
                continue;
            }

            if ($rule->isProspectMinAgeRule()) {
                $prospectMinAgeRule = (int) $rule->getValue();
                continue;
            }

            if ($rule->isProspectMaxAgeRule()) {
                $prospectMaxAgeRule = (int) $rule->getValue();
                continue;
            }

            if ($rule->isMinHomeAgeRule()) {
                $minHomeAgeRule = (int) $rule->getValue();
                continue;
            }

            if ($rule->isMinEstimatedIncomeFilterRule()) {
                $minEstimatedIncome = (int) $rule->getValue();
                continue;
            }

            if ($rule->isPostalCodeLimitsFilterRule()) {
                $postalCodes = json_decode($rule->getValue(), true, 512, JSON_THROW_ON_ERROR);
                continue;
            }

            if ($rule->isProspectTagsFilterRule()) {
                $tags = json_decode($rule->getValue(), true, 512, JSON_THROW_ON_ERROR);
                continue;
            }

            if ($rule->isAddressTypeInclusionRule()) {
                $addressTypeInclusionRule = $rule->getValue();
            }
        }

        return new self(
            intacctId: $intacctId,
            customerInclusionRule: $customerInclusionRule,
            lifetimeValueRule: $customerMaxLTVRule,
            clubMembersRule: $clubMembersInclusionRule,
            installationsRule: $installationsInclusionRule,
            prospectMinAge: $prospectMinAgeRule,
            prospectMaxAge: $prospectMaxAgeRule,
            minHomeAge: $minHomeAgeRule,
            minEstimatedIncome: $minEstimatedIncome,
            postalCodes: $postalCodes,
            tags: $tags,
            addressTypeRule: $addressTypeInclusionRule,
        );
    }
}

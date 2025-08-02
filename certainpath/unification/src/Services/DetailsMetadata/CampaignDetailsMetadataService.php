<?php

namespace App\Services\DetailsMetadata;

use App\Entity\ProspectFilterRule;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Repository\ProspectFilterRuleRepository;
use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;

readonly class CampaignDetailsMetadataService
{
    public function __construct(
        private ProspectFilterRuleRepository $prospectFilterRulesRepository
    ) {
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function getDetailsMetadata(): array
    {
        return [
            'mailingFrequencies' => $this->getMailingFrequencies(),
            'campaignTargets' => $this->getCampaignTargets(),
            'addressTypes' => $this->getAddressTypes(),
            'customerRestrictionCriteria' => $this->getCustomerRestrictionCriteria(),
            'estimatedIncomeOptions' => self::getMinEstimatedIncomeOptions(),
        ];
    }

    public function getMailingFrequencies(): array
    {
        $mailingFrequencies['Every week'] = 1;

        for ($i = 2; $i <= 12; $i++) {
            $mailingFrequencies['Every ' . $i . ' weeks'] = $i;
        }

        return self::structureArray($mailingFrequencies);
    }

    public static function getMinEstimatedIncomeOptions(): array
    {
        $minEstimatedIncomeOptions = [
            '$0' => 1,
            '$15,000' => 2,
            '$20,000' => 3,
            '$30,000' => 4,
            '$40,000' => 5,
            '$50,000' => 6,
            '$75,000' => 7,
            '$100,000' => 8,
            '$125,000' => 9,
        ];

        return self::structureArray($minEstimatedIncomeOptions);
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function getCampaignTargets(): array
    {
        $rules = $this->prospectFilterRulesRepository->findAllByNameOrFail(
            ProspectFilterRuleRegistry::CUSTOMER_INCLUSION_RULE_NAME
        );

        return array_map(static fn($rule) => [
            'name' => $rule->getDisplayedName(),
            'value' => $rule->getValue(),
        ], $rules->toArray());
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function getAddressTypes()
    {
        $rules = $this->prospectFilterRulesRepository->findAllByNameOrFail(
            ProspectFilterRuleRegistry::ADDRESS_TYPE_INCLUSION_RULE_NAME
        );

        return array_map(static fn($rule) => [
            'name' => $rule->getDisplayedName(),
            'value' => $rule->getValue(),
        ], $rules->toArray());
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function getCustomerRestrictionCriteria(): array
    {
        $rules = [];

        $excludeCustomerLtv5000Rule = $this->prospectFilterRulesRepository->findByNameAndValueOrFail(
            ProspectFilterRuleRegistry::CUSTOMER_MAX_LTV_RULE_NAME,
            ProspectFilterRuleRegistry::CUSTOMER_MAX_LTV_5000_VALUE
        );

        $excludeClubMembersRule = $this->prospectFilterRulesRepository->findByNameAndValueOrFail(
            ProspectFilterRuleRegistry::CLUB_MEMBERS_INCLUSION_RULE_NAME,
            ProspectFilterRuleRegistry::EXCLUDE_CLUB_MEMBERS_VALUE
        );

        $excludeCustomerInstallationsRule = $this->prospectFilterRulesRepository->findByNameAndValueOrFail(
            ProspectFilterRuleRegistry::CUSTOMER_INSTALLATIONS_INCLUSION_RULE_NAME,
            ProspectFilterRuleRegistry::EXCLUDE_CUSTOMER_INSTALLATIONS_VALUE
        );

        $rules[] = $excludeCustomerLtv5000Rule;
        $rules[] = $excludeClubMembersRule;
        $rules[] = $excludeCustomerInstallationsRule;

        /** @var ProspectFilterRule $rule */
        return array_map(static fn($rule) => [
            'name' => $rule->getDisplayedName(),
            'value' => $rule->getValue(),
        ], $rules);
    }

    private static function structureArray(array $array): array
    {
        return array_map(static fn($key, $item) => [
            'name' => $key,
            'value' => $item,
        ], array_keys($array), $array);
    }
}

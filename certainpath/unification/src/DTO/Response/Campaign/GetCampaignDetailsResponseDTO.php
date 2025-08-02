<?php

namespace App\DTO\Response\Campaign;

use App\Entity\Campaign;
use App\Entity\Location;
use App\Entity\ProspectFilterRule;
use App\Services\DetailsMetadata\CampaignDetailsMetadataService;
use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;

readonly class GetCampaignDetailsResponseDTO
{
    public function __construct(
        public int $id,
        public string $intacctId,
        public string $name,
        public ?string $phoneNumber,
        public ?string $description,
        public \DateTimeInterface $startDate,
        public \DateTimeInterface $endDate,
        public ?string $hubPlusProductId,
        public bool $canBePaused,
        public bool $canBeResumed,
        public bool $canBeStopped,
        public int $totalProspects,
        public array $campaignStatus,
        public array $locations,
        public array $mailingSchedule,
        public array $filters,
        public array $postalCodeLimits,
        public bool $showDemographicTargets,
        public bool $showTagSelector,
        public bool $showCustomerRestrictionCriteria,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public static function fromEntity(Campaign $campaign): self
    {
        $postalCodeLimits = self::preparePostalCodeLimitsData($campaign);
        $totalProspects = self::calculateTotalProspects($postalCodeLimits);
        $campaignStatus = self::prepareCampaignStatusData($campaign);
        $locations = self::prepareLocationsData($campaign);
        $mailingSchedule = self::prepareMailingScheduleData($campaign, $totalProspects);
        $campaignTarget = self::prepareCampaignTargetRulesData($campaign);
        $addressType = self::prepareAddressTypeRuleData($campaign);
        $demographicTargets = self::prepareDemographicTargetsData($campaign);
        $customerRestrictionCriteria = self::prepareCustomerRestrictionCriteriaData($campaign);
        $postalCodeLimits = self::preparePostalCodeLimitsData($campaign);
        $tags = self::prepareProspectTagsRuleData($campaign);
        $audience = $campaignTarget['value'] ?? null;

        return new self(
            id: $campaign->getId(),
            intacctId: $campaign->getCompany()?->getIdentifier(),
            name: $campaign->getName(),
            phoneNumber: $campaign->getPhoneNumber(),
            description: $campaign->getDescription(),
            startDate: $campaign->getStartDate(),
            endDate: $campaign->getEndDate(),
            hubPlusProductId: $campaign->getHubPlusProductId(),
            canBePaused: $campaign->canBePaused(),
            canBeResumed: $campaign->canBeResumed(),
            canBeStopped: $campaign->canBeStopped(),
            totalProspects: $totalProspects,
            campaignStatus: $campaignStatus,
            locations: $locations,
            mailingSchedule: $mailingSchedule,
            filters: [
                'tags' => $tags,
                'campaignTarget' => $campaignTarget,
                'addressType' => $addressType,
                'demographicTargets' => $demographicTargets,
                'customerRestrictionCriteria' => $customerRestrictionCriteria,
            ],
            postalCodeLimits: $postalCodeLimits,
            showDemographicTargets: $audience !== ProspectFilterRuleRegistry::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_VALUE,
            showTagSelector: $audience !== ProspectFilterRuleRegistry::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_VALUE,
            showCustomerRestrictionCriteria: $audience !== ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_ONLY_VALUE,
        );
    }

    private static function isDemographicTargetRule(ProspectFilterRule $rule): bool
    {
        return in_array($rule->getName(), [
            ProspectFilterRuleRegistry::HOME_MIN_AGE_RULE_NAME,
            ProspectFilterRuleRegistry::PROSPECT_MIN_AGE_RULE_NAME,
            ProspectFilterRuleRegistry::PROSPECT_MAX_AGE_RULE_NAME,
            ProspectFilterRuleRegistry::MIN_ESTIMATED_INCOME_RULE_NAME,
        ], true);
    }

    private static function isCustomerRestrictionRule(ProspectFilterRule $rule): bool
    {
        return in_array($rule->getName(), [
            ProspectFilterRuleRegistry::CUSTOMER_MAX_LTV_RULE_NAME,
            ProspectFilterRuleRegistry::CUSTOMER_INSTALLATIONS_INCLUSION_RULE_NAME,
            ProspectFilterRuleRegistry::CLUB_MEMBERS_INCLUSION_RULE_NAME,
        ], true);
    }

    private static function isAddressTypeRule(ProspectFilterRule $rule): bool
    {
        return $rule->getName() === ProspectFilterRuleRegistry::ADDRESS_TYPE_INCLUSION_RULE_NAME;
    }

    private static function isCampaignTargetRule(ProspectFilterRule $rule): bool
    {
        return $rule->getName() === ProspectFilterRuleRegistry::CUSTOMER_INCLUSION_RULE_NAME;
    }

    private static function isProspectTagsRule(ProspectFilterRule $rule): bool
    {
        return $rule->getName() === ProspectFilterRuleRegistry::PROSPECT_TAGS_RULE_NAME;
    }

    private static function isPostalCodeLimitsRule(ProspectFilterRule $rule): bool
    {
        return $rule->getName() === ProspectFilterRuleRegistry::POSTAL_CODE_LIMITS_RULE_NAME;
    }

    private static function prepareDemographicTargetsData(Campaign $campaign): array
    {
        $demographicTargetRules = [];

        /** @var ProspectFilterRule $prospectFilterRule */
        foreach ($campaign->getProspectFilterRules() as $prospectFilterRule) {
            if (!self::isDemographicTargetRule($prospectFilterRule)) {
                continue;
            }

            $ruleName = $prospectFilterRule->getName();
            $ruleValue = $prospectFilterRule->getValue();

            if ($ruleName === ProspectFilterRuleRegistry::MIN_ESTIMATED_INCOME_RULE_NAME) {
                $matchedName = '';
                $incomeOptions = CampaignDetailsMetadataService::getMinEstimatedIncomeOptions();

                foreach ($incomeOptions as $option) {
                    if (($option['value'] ?? null) === (int)$ruleValue) {
                        $matchedName = $option['name'] ?? '';
                        break;
                    }
                }
                $value = $matchedName;
            } else {
                $value = $ruleValue;
            }

            $demographicTargetRules[] = [
                'name' => $ruleName,
                'value' => $value,
            ];
        }

        return $demographicTargetRules;
    }


    private static function prepareCampaignTargetRulesData(Campaign $campaign): array
    {
        foreach ($campaign->getProspectFilterRules() as $rule) {
            if (!self::isCampaignTargetRule($rule)) {
                continue;
            }

            return [
                'name' => $rule->getName(),
                'value' => $rule->getValue(),
            ];
        }

        return [];
    }

    private static function prepareAddressTypeRuleData(Campaign $campaign): array
    {
        foreach ($campaign->getProspectFilterRules() as $rule) {
            if (!self::isAddressTypeRule($rule)) {
                continue;
            }

            return [
                'name' => $rule->getName(),
                'value' => $rule->getValue(),
            ];
        }

        return [];
    }

    /**
     * @throws \JsonException
     */
    private static function prepareProspectTagsRuleData(Campaign $campaign): array
    {
        foreach ($campaign->getProspectFilterRules() as $rule) {
            if (!self::isProspectTagsRule($rule)) {
                continue;
            }

            $value = $rule->getValue();
            $tags = match (true) {
                is_string($value) => json_decode($value, true, 512, JSON_THROW_ON_ERROR),
                is_array($value) => $value,
                default => [],
            };

            return array_map(
                static fn($tag) => ['name' => $tag],
                is_array($tags) ? $tags : []
            );
        }

        return [];
    }

    private static function prepareMailingScheduleData(Campaign $campaign, int $totalProspects): array
    {
        $frequency = $campaign->getMailingFrequencyWeeks();
        $mailingDropWeeks = $campaign->getMailingDropWeeks();
        $dropWeekCount = count($mailingDropWeeks);
        
        $baseMailingCount = $dropWeekCount > 0 ? (int)($totalProspects / $dropWeekCount) : 0;
        $remainder = $dropWeekCount > 0 ? $totalProspects % $dropWeekCount : 0;

        $mailingScheduleData = [
            'mailingFrequency' => [
                'value' => $frequency,
                'label' => $frequency === 1 ? 'Every week' : "Every {$frequency} weeks",
            ],
            'mailingDropWeeks' => [],
        ];

        foreach ($mailingDropWeeks as $index => $mailingDropWeek) {
            $mailingScheduleData['mailingDropWeeks'][] = [
                'weekNumber' => $mailingDropWeek,
                'mailingCount' => $baseMailingCount + ($index === $dropWeekCount - 1 ? $remainder : 0),
            ];
        }

        return $mailingScheduleData;
    }

    private static function prepareLocationsData(Campaign $campaign): array
    {
        return array_map(
            static fn(Location $location) => [
                'id' => $location->getId(),
                'name' => $location->getName(),
            ],
            $campaign->getLocations()->toArray()
        );
    }

    /**
     * @throws \JsonException
     */
    private static function preparePostalCodeLimitsData(Campaign $campaign): array
    {
        $postalCodeLimitsData = [];

        foreach ($campaign->getProspectFilterRules() as $rule) {
            if (!self::isPostalCodeLimitsRule($rule)) {
                continue;
            }

            $decoded = json_decode($rule->getValue(), true, 512, JSON_THROW_ON_ERROR);
            
            foreach ($decoded as $postalCode => $limit) {
                $postalCodeLimitsData[] = [
                    'postalCode' => $postalCode,
                    'limit' => $limit,
                ];
            }
        }

        return $postalCodeLimitsData;
    }

    private static function prepareCustomerRestrictionCriteriaData(Campaign $campaign): array
    {
        $criteria = [];

        foreach ($campaign->getProspectFilterRules() as $rule) {
            if (!self::isCustomerRestrictionRule($rule)) {
                continue;
            }

            $criteria[] = [
                'name' => $rule->getName(),
                'value' => $rule->getValue(),
            ];
        }

        return $criteria;
    }

    private static function prepareCampaignStatusData(Campaign $campaign): array
    {
        $campaignStatus = $campaign->getCampaignStatus();

        return $campaignStatus ? [
            'id' => $campaignStatus->getId(),
            'name' => $campaignStatus->getName(),
        ] : [];
    }
    
    private static function calculateTotalProspects(array $postalCodeLimits): int
    {
        return array_reduce(
            $postalCodeLimits,
            static fn(int $total, array $data) => $total + (int) $data['limit'],
            0
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phoneNumber' => $this->phoneNumber,
            'description' => $this->description,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'hubPlusProductId' => $this->hubPlusProductId,
            'canBePaused' => $this->canBePaused,
            'canBeResumed' => $this->canBeResumed,
            'canBeStopped' => $this->canBeStopped,
            'totalProspects' => $this->totalProspects,
            'campaignStatus' => $this->campaignStatus,
            'locations' => $this->locations,
            'mailingSchedule' => $this->mailingSchedule,
            'filters' => $this->filters,
            'postalCodeLimits' => $this->postalCodeLimits,
            'showDemographicTargets' => $this->showDemographicTargets,
            'showTagSelector' => $this->showTagSelector,
            'showCustomerRestrictionCriteria' => $this->showCustomerRestrictionCriteria,
        ];
    }
}
<?php

namespace App\Services\ProspectFilterRule;

use App\DTO\Domain\ProspectFilterRulesDTO;
use App\Entity\ProspectFilterRule;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Repository\ProspectFilterRuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use JsonException;

readonly class ProspectFilterRuleService
{
    public function __construct(
        private ProspectFilterRuleRepository $filterRulesRepository
    ) {
    }

    /**
     * @throws JsonException
     * @throws ProspectFilterRuleNotFoundException
     */
    public function prepareProspectFilterRulesFromDTO(ProspectFilterRulesDTO $dto): ArrayCollection
    {
        $filterRules = new ArrayCollection();

        if ($dto->customerInclusionRule) {
            $customerInclusionRule = $this->filterRulesRepository->findByNameAndValueOrFail(
                ProspectFilterRuleRegistry::CUSTOMER_INCLUSION_RULE_NAME,
                $dto->customerInclusionRule
            );
            $filterRules->add($customerInclusionRule);
        }

        if ($postalCodeValues = $dto->postalCodes) {
            $postalCodesRule = $this->getOrCreatePostalCodesFilterRule($postalCodeValues);
            $filterRules->add($postalCodesRule);
        }

        if ($tagValues = $dto->tags) {
            $tagRule = $this->getOrCreateTagsFilterRule($tagValues);
            $filterRules->add($tagRule);
        }

        // Prepare Campaign Target Rules
        if ($this->isCustomerInclusionRequested($dto->customerInclusionRule)) {
            if ($dto->lifetimeValueRule) {
                $maxLtvRule = $this->getOrCreateCustomerMaxLtvRule($dto->lifetimeValueRule);
                $filterRules->add($maxLtvRule);
            }

            if ($dto->clubMembersRule) {
                $clubMembersRule = $this->filterRulesRepository->findByNameAndValueOrFail(
                    ProspectFilterRuleRegistry::CLUB_MEMBERS_INCLUSION_RULE_NAME,
                    $dto->clubMembersRule
                );
                $filterRules->add($clubMembersRule);
            }

            if ($dto->installationsRule) {
                $customerInstallationsRule = $this->filterRulesRepository->findByNameAndValueOrFail(
                    ProspectFilterRuleRegistry::CUSTOMER_INSTALLATIONS_INCLUSION_RULE_NAME,
                    $dto->installationsRule
                );
                $filterRules->add($customerInstallationsRule);
            }
        }

        // Prepare Address Type Rule
        if ($dto->addressTypeRule) {
            $addressTypeRule = $this->filterRulesRepository->findByNameAndValueOrFail(
                ProspectFilterRuleRegistry::ADDRESS_TYPE_INCLUSION_RULE_NAME,
                $dto->addressTypeRule
            );
            $filterRules->add($addressTypeRule);
        }

        // Prepare Demographic Targets Rules
        if ($this->isProspectInclusionRequested($dto->customerInclusionRule)) {
            if ($prospectMinAgeValue = $dto->prospectMinAge) {
                $prospectMinAgeRule = $this->getOrCreateProspectMinAgeFilterRule($prospectMinAgeValue);
                $filterRules->add($prospectMinAgeRule);
            }

            if ($prospectMaxAgeValue = $dto->prospectMaxAge) {
                $prospectMaxAgeRule = $this->getOrCreateProspectMaxAgeFilterRule($prospectMaxAgeValue);
                $filterRules->add($prospectMaxAgeRule);
            }

            if ($minHomeAgeRuleValue = $dto->minHomeAge) {
                $minHomeAgeRule = $this->getOrCreateHomeMinAgeFilterRule($minHomeAgeRuleValue);
                $filterRules->add($minHomeAgeRule);
            }

            if ($minEstimatedIncomeValue = $dto->minEstimatedIncome) {
                $minEstimatedIncomeRule = $this->getOrCreateMinEstimatedIncomeFilterRule($minEstimatedIncomeValue);
                $filterRules->add($minEstimatedIncomeRule);
            }
        }

        return $filterRules;
    }

    private function isCustomerInclusionRequested(string $customerInclusionRule): bool
    {
        return in_array($customerInclusionRule, [
            ProspectFilterRuleRegistry::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_VALUE,
            ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_AND_CUSTOMERS_VALUE
        ], true);
    }

    private function isProspectInclusionRequested(string $customerInclusionRule): bool
    {
        return in_array($customerInclusionRule, [
            ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_ONLY_VALUE,
            ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_AND_CUSTOMERS_VALUE
        ], true);
    }

    public function createRule(
        string $name,
        string $displayedName,
        mixed $value,
        string $description = null
    ): ProspectFilterRule {
        $newRule = (new ProspectFilterRule())
            ->setName($name)
            ->setDisplayedName($displayedName)
            ->setValue($value)
            ->setDescription($description);

        return $this->filterRulesRepository->saveProspectFilterRule($newRule);
    }

    public function getOrCreateRule(
        string $name,
        string $displayedName,
        mixed $value,
        string $description = null
    ): ProspectFilterRule {
        $existingRule = $value
            ? $this->filterRulesRepository->findByNameAndValue($name, $value)
            : $this->filterRulesRepository->findByName($name);

        if ($existingRule) {
            return $existingRule;
        }

        return $this->createRule(
            $name,
            $displayedName,
            $value,
            $description
        );
    }

    public function getOrCreateCustomerMinLtvRule(int $value): ProspectFilterRule
    {
        $ruleDefinition = ProspectFilterRuleRegistry::generateCustomerMinLtvFilterRuleDefinition($value);

        return $this->getOrCreateRule(
            $ruleDefinition['name'],
            $ruleDefinition['displayedName'],
            $ruleDefinition['value'],
            $ruleDefinition['description']
        );
    }

    public function getOrCreateCustomerMaxLtvRule(int $value): ProspectFilterRule
    {
        $ruleDefinition = ProspectFilterRuleRegistry::generateCustomerMaxLtvFilterRuleDefinition($value);

        return $this->getOrCreateRule(
            $ruleDefinition['name'],
            $ruleDefinition['displayedName'],
            $ruleDefinition['value'],
            $ruleDefinition['description']
        );
    }

    public function getOrCreateProspectMinAgeFilterRule(int $value): ProspectFilterRule
    {
        $ruleDefinition = ProspectFilterRuleRegistry::generateProspectMinAgeFilterRuleDefinition($value);

        return $this->getOrCreateRule(
            $ruleDefinition['name'],
            $ruleDefinition['displayedName'],
            $ruleDefinition['value'],
            $ruleDefinition['description']
        );
    }

    private function getOrCreateProspectMaxAgeFilterRule(int $prospectMaxAgeValue): ProspectFilterRule
    {
        $ruleDefinition = ProspectFilterRuleRegistry::prepareProspectMaxAgeFilterRuleDefinition($prospectMaxAgeValue);

        return $this->getOrCreateRule(
            $ruleDefinition['name'],
            $ruleDefinition['displayedName'],
            $ruleDefinition['value'],
            $ruleDefinition['description']
        );
    }

    private function getOrCreateHomeMinAgeFilterRule(int $homeAgeRuleValue): ProspectFilterRule
    {
        $ruleDefinition = ProspectFilterRuleRegistry::generateHomeMinAgeFilterRuleDefinition($homeAgeRuleValue);

        return $this->getOrCreateRule(
            $ruleDefinition['name'],
            $ruleDefinition['displayedName'],
            $ruleDefinition['value'],
            $ruleDefinition['description']
        );
    }

    private function getOrCreateMinEstimatedIncomeFilterRule(string $minEstimatedIncome): ProspectFilterRule
    {
        $ruleDefinition = ProspectFilterRuleRegistry::generateMinEstimatedIncomeFilterRuleDefinition(
            $minEstimatedIncome
        );

        return $this->getOrCreateRule(
            $ruleDefinition['name'],
            $ruleDefinition['displayedName'],
            $ruleDefinition['value'],
            $ruleDefinition['description']
        );
    }

    /**
     * @throws JsonException
     */
    private function getOrCreateTagsFilterRule(array $tags): ProspectFilterRule
    {
        $ruleDefinition = ProspectFilterRuleRegistry::generateTagsFilterRuleDefinition($tags);

        return $this->getOrcreateRule(
            $ruleDefinition['name'],
            $ruleDefinition['displayedName'],
            $ruleDefinition['value'],
            $ruleDefinition['description']
        );
    }

    /**
     * @throws JsonException
     */
    private function getOrCreatePostalCodesFilterRule(array $postalCodes): ProspectFilterRule
    {
        $ruleDefinition = ProspectFilterRuleRegistry::generatePostalCodesFilterRuleDefinition($postalCodes);

        return $this->getOrcreateRule(
            $ruleDefinition['name'],
            $ruleDefinition['displayedName'],
            $ruleDefinition['value'],
            $ruleDefinition['description']
        );
    }
}

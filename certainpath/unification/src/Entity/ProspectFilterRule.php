<?php

namespace App\Entity;

use App\Repository\ProspectFilterRuleRepository;
use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProspectFilterRuleRepository::class)]
#[ORM\UniqueConstraint(name: "name_value_uniq", columns: ["name", "value"])]
class ProspectFilterRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $displayedName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $value = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDisplayedName(): ?string
    {
        return $this->displayedName;
    }

    public function setDisplayedName(string $displayedName): static
    {
        $this->displayedName = $displayedName;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isCustomerInclusionRule(): bool
    {
        $ruleAvailableValues = [
            ProspectFilterRuleRegistry::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_VALUE,
            ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_ONLY_VALUE,
            ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_AND_CUSTOMERS_VALUE
        ];

        return (
            $this->name === ProspectFilterRuleRegistry::CUSTOMER_INCLUSION_RULE_NAME &&
            in_array($this->value, $ruleAvailableValues, true)
        );
    }

    public function isAddressTypeInclusionRule(): bool
    {
        $ruleAvailableValues = [
            ProspectFilterRuleRegistry::INCLUDE_COMMERCIAL_ONLY_RULE_VALUE,
            ProspectFilterRuleRegistry::INCLUDE_RESIDENTIAL_ONLY_RULE_VALUE,
            ProspectFilterRuleRegistry::INCLUDE_BOTH_RESIDENTIAL_AND_COMMERCIAL_RULE_DISPLAYED_NAME,
        ];

        return (
            $this->name === ProspectFilterRuleRegistry::ADDRESS_TYPE_INCLUSION_RULE_NAME &&
            in_array($this->value, $ruleAvailableValues, true)
        );
    }

    public function isClubMembersInclusionRule(): bool
    {
        $ruleAvailableValues = [
            ProspectFilterRuleRegistry::INCLUDE_CLUB_MEMBERS_ONLY_VALUE,
            ProspectFilterRuleRegistry::EXCLUDE_CLUB_MEMBERS_VALUE
        ];

        return (
            $this->name === ProspectFilterRuleRegistry::CLUB_MEMBERS_INCLUSION_RULE_NAME &&
            in_array($this->value, $ruleAvailableValues, true)
        );
    }

    public function isCustomerInstallationsInclusionRule(): bool
    {
        $ruleAvailableValues = [
            ProspectFilterRuleRegistry::INCLUDE_CUSTOMER_INSTALLATIONS_ONLY_VALUE,
            ProspectFilterRuleRegistry::EXCLUDE_CUSTOMER_INSTALLATIONS_VALUE
        ];

        return (
            $this->name === ProspectFilterRuleRegistry::CUSTOMER_INSTALLATIONS_INCLUSION_RULE_NAME &&
            in_array($this->value, $ruleAvailableValues, true)
        );
    }

    public function isCustomerMinLtvRule(): bool
    {
        return $this->name === ProspectFilterRuleRegistry::CUSTOMER_MIN_LTV_RULE_NAME;
    }

    public function isCustomerMaxLtvRule(): bool
    {
        return $this->name === ProspectFilterRuleRegistry::CUSTOMER_MAX_LTV_RULE_NAME;
    }

    public function isProspectMinAgeRule(): bool
    {
        return $this->name === ProspectFilterRuleRegistry::PROSPECT_MIN_AGE_RULE_NAME;
    }

    public function isProspectMaxAgeRule(): bool
    {
        return $this->name === ProspectFilterRuleRegistry::PROSPECT_MAX_AGE_RULE_NAME;
    }

    public function isMinHomeAgeRule(): bool
    {
        return $this->name === ProspectFilterRuleRegistry::HOME_MIN_AGE_RULE_NAME;
    }

    public function isPostalCodeLimitsFilterRule(): bool
    {
        return $this->name === ProspectFilterRuleRegistry::POSTAL_CODE_LIMITS_RULE_NAME;
    }

    public function isProspectTagsFilterRule(): bool
    {
        return $this->name === ProspectFilterRuleRegistry::PROSPECT_TAGS_RULE_NAME;
    }

    public function isMinEstimatedIncomeFilterRule(): bool
    {
        return $this->name === ProspectFilterRuleRegistry::MIN_ESTIMATED_INCOME_RULE_NAME;
    }
}

<?php

namespace App\Services\ProspectFilterRule;

use App\Services\DetailsMetadata\CampaignDetailsMetadataService;
use JsonException;

class ProspectFilterRuleRegistry
{
    /**
     * ================================================================================================================
     * RULE NAMES
     * ================================================================================================================
     */
    public const CUSTOMER_INCLUSION_RULE_NAME = 'customer_inclusion';
    public const CLUB_MEMBERS_INCLUSION_RULE_NAME = 'club_members_inclusion';
    public const CUSTOMER_INSTALLATIONS_INCLUSION_RULE_NAME = 'customer_installations_inclusion';
    public const CUSTOMER_MIN_LTV_RULE_NAME = 'customer_min_ltv';
    public const CUSTOMER_MAX_LTV_RULE_NAME = 'customer_max_ltv';
    public const PROSPECT_MIN_AGE_RULE_NAME = 'prospect_min_age';
    public const PROSPECT_MAX_AGE_RULE_NAME = 'prospect_max_age';
    public const HOME_MIN_AGE_RULE_NAME = 'home_min_age';
    public const HOME_MAX_AGE_RULE_NAME = 'home_max_age';
    public const MIN_ESTIMATED_INCOME_RULE_NAME = 'min_estimated_income';
    public const POSTAL_CODE_LIMITS_RULE_NAME = 'postal_code_limits';
    public const PROSPECT_TAGS_RULE_NAME = 'prospect_tags';
    public const ADDRESS_TYPE_INCLUSION_RULE_NAME = 'address_type_inclusion';

    /**
     * ================================================================================================================
     * RULE DISPLAYED NAMES
     * ================================================================================================================
     */

    /**
     * CUSTOMER INCLUSION RULE DISPLAYED NAMES
     */
    public const INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_DISPLAYED_NAME = 'Active customers only';
    public const INCLUDE_PROSPECTS_ONLY_RULE_DISPLAYED_NAME = 'Prospects only';
    public const INCLUDE_PROSPECTS_AND_CUSTOMERS_RULE_DISPLAYED_NAME = 'Both customers and prospects';

    /**
     * CUSTOMER LIFETIME VALUE RULE DISPLAYED NAMES
     */
    public const CUSTOMER_MIN_LTV_RULE_DISPLAYED_NAME = 'Exclude customers with LTV less than';
    public const CUSTOMER_MAX_LTV_RULE_DISPLAYED_NAME = 'Exclude customers with LTV greater than';
    public const CUSTOMER_MAX_LTV_5000_RULE_DISPLAYED_NAME =
        self::CUSTOMER_MAX_LTV_RULE_DISPLAYED_NAME . ' $' . self::CUSTOMER_MAX_LTV_5000_VALUE;

    /**
     * CLUB MEMBERS INCLUSION RULE DISPLAYED NAMES
     */
    public const INCLUDE_CLUB_MEMBERS_ONLY_DISPLAYED_NAME = 'Include club members only';
    public const EXCLUDE_CLUB_MEMBERS_DISPLAYED_NAME = 'Exclude club members';

    /**
     * CUSTOMER INSTALLATIONS INCLUSION RULE DISPLAYED NAMES
     */
    public const INCLUDE_CUSTOMER_INSTALLATIONS_ONLY_DISPLAYED_NAME = 'Include customer installations only';
    public const EXCLUDE_CUSTOMER_INSTALLATIONS_DISPLAYED_NAME = 'Exclude customer installations';

    /**
     * PROSPECT AGE RULE DISPLAYED NAMES
     */
    public const PROSPECT_MIN_AGE_RULE_DISPLAYED_NAME = 'Minimum Prospect Age';
    public const PROSPECT_MAX_AGE_RULE_DISPLAYED_NAME = 'Maximum Prospect Age';

    /**
     * HOME AGE RULE DISPLAYED NAMES
     */
    public const HOME_MIN_AGE_RULE_DISPLAYED_NAME = 'Minimum Home Age';
    public const HOME_MAX_AGE_RULE_DISPLAYED_NAME = 'Maximum Home Age';

    /**
     * POSTAL CODE LIMITS RULE DISPLAYED NAMES
     */
    public const POSTAL_CODE_LIMITS_RULE_DISPLAYED_NAME = 'Postal code limits';

    /**
     * PROSPECT TAGS RULE DISPLAYED NAMES
     */
    public const PROSPECT_TAGS_RULE_DISPLAYED_NAME = 'Prospect tags';

    /**
     * ESTIMATED INCOME DISPLAYED NAMES
     */
    public const MIN_ESTIMATED_INCOME_DISPLAYED_NAME = 'Minimum Estimated Income';

    /**
     * ADDRESS TYPE INCLUSION RULE DISPLAYED NAMES
     */
    public const INCLUDE_RESIDENTIAL_ONLY_RULE_DISPLAYED_NAME = 'Residential';
    public const INCLUDE_COMMERCIAL_ONLY_RULE_DISPLAYED_NAME = 'Commercial';
    public const INCLUDE_BOTH_RESIDENTIAL_AND_COMMERCIAL_RULE_DISPLAYED_NAME = 'Both residential and commercial';

    /**
     * ================================================================================================================
     * RULE VALUES
     * ================================================================================================================
     */

    /**
     * CUSTOMER INCLUSION RULE VALUES
     */
    public const INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_VALUE = 'include_active_customers_only';
    public const INCLUDE_PROSPECTS_ONLY_VALUE = 'include_prospects_only';
    public const INCLUDE_PROSPECTS_AND_CUSTOMERS_VALUE = 'include_prospects_and_customers';

    /**
     * CUSTOMER INSTALLATIONS INCLUSION RULE VALUES
     */
    public const INCLUDE_CUSTOMER_INSTALLATIONS_ONLY_VALUE = 'include_customer_installations_only';
    public const EXCLUDE_CUSTOMER_INSTALLATIONS_VALUE = 'exclude_customer_installations';

    /**
     * CLUB MEMBERS INCLUSION RULE VALUES
     */
    public const INCLUDE_CLUB_MEMBERS_ONLY_VALUE = 'include_club_members_only';
    public const EXCLUDE_CLUB_MEMBERS_VALUE = 'exclude_club_members';

    /**
     * CUSTOMER LIFETIME VALUE RULE VALUES
     */
    public const CUSTOMER_MAX_LTV_5000_VALUE = 5000;

    /**
     * ADDRESS TYPE INCLUSION RULE VALUES
     */
    public const INCLUDE_RESIDENTIAL_ONLY_RULE_VALUE = 'include_residential_only';
    public const INCLUDE_COMMERCIAL_ONLY_RULE_VALUE = 'include_commercial_only';
    public const INCLUDE_BOTH_RESIDENTIAL_AND_COMMERCIAL_RULE_VALUE = 'include_both_residential_and_commercial';

    /**
     * ================================================================================================================
     * RULE DESCRIPTIONS
     * ================================================================================================================
     */

    /**
     * CUSTOMER INCLUSION RULE DESCRIPTIONS
     */
    public const INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_DESCRIPTION = 'Include active customers only, exclude prospects';
    public const INCLUDE_PROSPECTS_ONLY_DESCRIPTION = 'Include prospects only, exclude customers';
    public const INCLUDE_PROSPECTS_AND_CUSTOMERS_DESCRIPTION = 'Include both prospects and customers';

    /**
     * CUSTOMER LIFETIME VALUE RULE DESCRIPTIONS
     */
    public const CUSTOMER_MAX_LTV_RULE_DESCRIPTION = 'Exclude prospects with LTV greater than';
    public const CUSTOMER_MAX_LTV_5000_RULE_DESCRIPTION =
        self::CUSTOMER_MAX_LTV_RULE_DESCRIPTION . ' $' . self::CUSTOMER_MAX_LTV_5000_VALUE;
    public const CUSTOMER_MIN_LTV_RULE_DESCRIPTION = 'Exclude prospects with LTV less than';

    /**
     * CLUB MEMBERS INCLUSION RULE DESCRIPTIONS
     */
    public const INCLUDE_CLUB_MEMBERS_ONLY_RULE_DESCRIPTION = 'Include club members only';
    public const EXCLUDE_CLUB_MEMBERS_RULE_DESCRIPTION = 'Exclude club members';

    /**
     * CUSTOMER INSTALLATIONS INCLUSION RULE DESCRIPTIONS
     */
    public const INCLUDE_CUSTOMER_INSTALLATIONS_ONLY_DESCRIPTION = 'Include customers with installations only';
    public const EXCLUDE_CUSTOMER_INSTALLATIONS_DESCRIPTION = 'Exclude customers with installations';

    /**
     * PROSPECT AGE RULE DESCRIPTIONS
     */
    public const PROSPECT_MIN_AGE_RULE_DESCRIPTION = 'Exclude prospects younger than';
    public const PROSPECT_MAX_AGE_RULE_DESCRIPTION = 'Exclude prospects older than';

    /**
     * HOME AGE RULE DESCRIPTIONS
     */
    public const HOME_MIN_AGE_RULE_DESCRIPTION = 'Exclude prospects with homes younger than';
    public const HOME_MAX_AGE_RULE_DESCRIPTION = 'Exclude prospects with homes older than';

    /**
     * NET WORTH RULE DESCRIPTIONS
     */
    public const MIN_NET_WORTH_RULE_DESCRIPTION = 'Exclude prospects with net worth less than';

    /**
     * POSTAL CODE LIMITS RULE DESCRIPTIONS
     */
    public const POSTAL_CODE_LIMITS_RULE_DESCRIPTION = 'Include by postal codes and postal code limits';

    /**
     * PROSPECT TAGS RULE DESCRIPTIONS
     */
    public const PROSPECT_TAGS_RULE_DESCRIPTION = 'Include by prospect tags';

    /**
     * ESTIMATED INCOME DESCRIPTIONS
     */
    public const MIN_ESTIMATED_INCOME_DESCRIPTION = 'Exclude prospects with estimated income less than';

    /**
     * ADDRESS TYPE INCLUSION RULE DESCRIPTIONS
     */
    public const INCLUDE_RESIDENTIAL_ONLY_RULE_DESCRIPTION = 'Include residential addresses only, exclude commercial';
    public const INCLUDE_COMMERCIAL_ONLY_RULE_DESCRIPTION = 'Include commercial addresses only, exclude residential';
    public const INCLUDE_BOTH_RESIDENTIAL_AND_COMMERCIAL_RULE_DESCRIPTION =
        'Include both residential and commercial addresses';

    public static function getStaticRuleDefinitions(): array
    {
        return [
            [
                'name' => self::CUSTOMER_INCLUSION_RULE_NAME,
                'displayedName' => self::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_DISPLAYED_NAME,
                'value' => self::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_VALUE,
                'description' => self::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_DESCRIPTION,
            ],
            [
                'name' => self::CUSTOMER_INCLUSION_RULE_NAME,
                'displayedName' => self::INCLUDE_PROSPECTS_ONLY_RULE_DISPLAYED_NAME,
                'value' => self::INCLUDE_PROSPECTS_ONLY_VALUE,
                'description' => self::INCLUDE_PROSPECTS_ONLY_DESCRIPTION,
            ],
            [
                'name' => self::CUSTOMER_INCLUSION_RULE_NAME,
                'displayedName' => self::INCLUDE_PROSPECTS_AND_CUSTOMERS_RULE_DISPLAYED_NAME,
                'value' => self::INCLUDE_PROSPECTS_AND_CUSTOMERS_VALUE,
                'description' => self::INCLUDE_PROSPECTS_AND_CUSTOMERS_DESCRIPTION,
            ],
            [
                'name' => self::CUSTOMER_MAX_LTV_RULE_NAME,
                'displayedName' => self::CUSTOMER_MAX_LTV_5000_RULE_DISPLAYED_NAME,
                'value' => self::CUSTOMER_MAX_LTV_5000_VALUE,
                'description' => self::CUSTOMER_MAX_LTV_5000_RULE_DESCRIPTION,
            ],
            [
                'name' => self::CLUB_MEMBERS_INCLUSION_RULE_NAME,
                'displayedName' => self::INCLUDE_CLUB_MEMBERS_ONLY_DISPLAYED_NAME,
                'value' => self::INCLUDE_CLUB_MEMBERS_ONLY_VALUE,
                'description' => self::INCLUDE_CLUB_MEMBERS_ONLY_RULE_DESCRIPTION,
            ],
            [
                'name' => self::CLUB_MEMBERS_INCLUSION_RULE_NAME,
                'displayedName' => self::EXCLUDE_CLUB_MEMBERS_DISPLAYED_NAME,
                'value' => self::EXCLUDE_CLUB_MEMBERS_VALUE,
                'description' => self::EXCLUDE_CLUB_MEMBERS_RULE_DESCRIPTION,
            ],
            [
                'name' => self::CUSTOMER_INSTALLATIONS_INCLUSION_RULE_NAME,
                'displayedName' => self::EXCLUDE_CUSTOMER_INSTALLATIONS_DISPLAYED_NAME,
                'value' => self::EXCLUDE_CUSTOMER_INSTALLATIONS_VALUE,
                'description' => self::EXCLUDE_CUSTOMER_INSTALLATIONS_DESCRIPTION,
            ],
            [
                'name' => self::CUSTOMER_INSTALLATIONS_INCLUSION_RULE_NAME,
                'displayedName' => self::INCLUDE_CUSTOMER_INSTALLATIONS_ONLY_DISPLAYED_NAME,
                'value' => self::INCLUDE_CUSTOMER_INSTALLATIONS_ONLY_VALUE,
                'description' => self::INCLUDE_CUSTOMER_INSTALLATIONS_ONLY_DESCRIPTION,
            ],
            [
                'name' => self::ADDRESS_TYPE_INCLUSION_RULE_NAME,
                'displayedName' => self::INCLUDE_COMMERCIAL_ONLY_RULE_DISPLAYED_NAME,
                'value' => self::INCLUDE_COMMERCIAL_ONLY_RULE_VALUE,
                'description' => self::INCLUDE_COMMERCIAL_ONLY_RULE_DESCRIPTION,
            ],
            [
                'name' => self::ADDRESS_TYPE_INCLUSION_RULE_NAME,
                'displayedName' => self::INCLUDE_RESIDENTIAL_ONLY_RULE_DISPLAYED_NAME,
                'value' => self::INCLUDE_RESIDENTIAL_ONLY_RULE_VALUE,
                'description' => self::INCLUDE_RESIDENTIAL_ONLY_RULE_DESCRIPTION,
            ],
            [
                'name' => self::ADDRESS_TYPE_INCLUSION_RULE_NAME,
                'displayedName' => self::INCLUDE_BOTH_RESIDENTIAL_AND_COMMERCIAL_RULE_DISPLAYED_NAME,
                'value' => self::INCLUDE_BOTH_RESIDENTIAL_AND_COMMERCIAL_RULE_VALUE,
                'description' => self::INCLUDE_BOTH_RESIDENTIAL_AND_COMMERCIAL_RULE_DESCRIPTION,
            ],
        ];
    }

    public static function generateProspectMinAgeFilterRuleDefinition(int $value): array
    {
        $ruleName = self::PROSPECT_MIN_AGE_RULE_NAME;
        $displayedName = self::PROSPECT_MIN_AGE_RULE_DISPLAYED_NAME;
        $description = self::PROSPECT_MIN_AGE_RULE_DESCRIPTION . ' ' . $value;

        return [
            'name' => $ruleName,
            'displayedName' => $displayedName,
            'value' => $value,
            'description' => $description,
        ];
    }

    public static function prepareProspectMaxAgeFilterRuleDefinition(int $value): array
    {
        $ruleName = self::PROSPECT_MAX_AGE_RULE_NAME;
        $displayedName = self::PROSPECT_MAX_AGE_RULE_DISPLAYED_NAME;
        $description = self::PROSPECT_MAX_AGE_RULE_DESCRIPTION . ' ' . $value;

        return [
            'name' => $ruleName,
            'displayedName' => $displayedName,
            'value' => $value,
            'description' => $description,
        ];
    }

    public static function generateHomeMinAgeFilterRuleDefinition(int $value): array
    {
        $ruleName = self::HOME_MIN_AGE_RULE_NAME;
        $displayedName = self::HOME_MIN_AGE_RULE_DISPLAYED_NAME;
        $description = self::HOME_MIN_AGE_RULE_DESCRIPTION . ' ' . $value;

        return [
            'name' => $ruleName,
            'displayedName' => $displayedName,
            'value' => $value,
            'description' => $description,
        ];
    }

    /**
     * If needed, implement logic in ProspectQueryBuilder::createFetchAllByProspectFilterRulesDTOQueryBuilder
     */
    public function generateHomeMaxAgeFilterRuleDefinition(int $value): array
    {
        $ruleName = self::HOME_MAX_AGE_RULE_NAME;
        $displayedName = self::HOME_MAX_AGE_RULE_DISPLAYED_NAME;
        $description = self::HOME_MAX_AGE_RULE_DESCRIPTION . ' ' . $value;

        return [
            'name' => $ruleName,
            'displayedName' => $displayedName,
            'value' => $value,
            'description' => $description,
        ];
    }

    public static function generateCustomerMinLtvFilterRuleDefinition(int $value): array
    {
        $ruleName = self::CUSTOMER_MAX_LTV_RULE_NAME;
        $displayedName = self::CUSTOMER_MIN_LTV_RULE_DISPLAYED_NAME . ' $' . $value;
        $description = self::CUSTOMER_MIN_LTV_RULE_DESCRIPTION . ' $' . $value;

        return [
            'name' => $ruleName,
            'displayedName' => $displayedName,
            'value' => $value,
            'description' => $description,
        ];
    }

    /**
     * If needed, implement logic in ProspectQueryBuilder::createFetchAllByProspectFilterRulesDTOQueryBuilder
     */
    public static function generateCustomerMaxLtvFilterRuleDefinition(int $value): array
    {
        $ruleName = self::CUSTOMER_MAX_LTV_RULE_NAME;
        $displayedName = self::CUSTOMER_MAX_LTV_RULE_DISPLAYED_NAME . ' $' . $value;
        $description = self::CUSTOMER_MAX_LTV_RULE_DESCRIPTION . ' $' . $value;

        return [
            'name' => $ruleName,
            'displayedName' => $displayedName,
            'value' => $value,
            'description' => $description,
        ];
    }

    /**
     * @throws JsonException
     */
    public static function generatePostalCodesFilterRuleDefinition(array $postalCodes): array
    {
        $ruleName = self::POSTAL_CODE_LIMITS_RULE_NAME;
        $displayedName = self::POSTAL_CODE_LIMITS_RULE_DISPLAYED_NAME;
        $description = self::POSTAL_CODE_LIMITS_RULE_DESCRIPTION;

        return [
            'name' => $ruleName,
            'displayedName' => $displayedName,
            'value' => json_encode($postalCodes, JSON_THROW_ON_ERROR),
            'description' => $description,
        ];
    }

    /**
     * @throws JsonException
     */
    public static function generateTagsFilterRuleDefinition(array $tags): array
    {
        $ruleName = self::PROSPECT_TAGS_RULE_NAME;
        $displayedName = self::PROSPECT_TAGS_RULE_DISPLAYED_NAME;
        $description = self::PROSPECT_TAGS_RULE_DESCRIPTION;

        return [
            'name' => $ruleName,
            'displayedName' => $displayedName,
            'value' => json_encode($tags, JSON_THROW_ON_ERROR),
            'description' => $description,
        ];
    }

    public static function generateMinEstimatedIncomeFilterRuleDefinition(int $value): array
    {
        $options = CampaignDetailsMetadataService::getMinEstimatedIncomeOptions();
        $estimatedIncomeName = $options[$value]['name'] ?? '';

        $ruleName = self::MIN_ESTIMATED_INCOME_RULE_NAME;
        $displayedName = self::MIN_ESTIMATED_INCOME_DISPLAYED_NAME . ' ' . $estimatedIncomeName;
        $description = self::MIN_ESTIMATED_INCOME_DESCRIPTION . ' ' . $estimatedIncomeName;

        return [
            'name' => $ruleName,
            'displayedName' => $displayedName,
            'value' => $value,
            'description' => $description,
        ];
    }
}

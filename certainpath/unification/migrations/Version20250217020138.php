<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250217020138 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            UPDATE prospect_filter_rule 
            SET 
                displayed_name = '" . ProspectFilterRuleRegistry::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_DISPLAYED_NAME . "',
                value = '" . ProspectFilterRuleRegistry::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_VALUE . "'
            WHERE name = 'include_customers_only'
        ");

        $this->addSql("
            UPDATE prospect_filter_rule 
            SET name = '" . ProspectFilterRuleRegistry::CUSTOMER_INCLUSION_RULE_NAME . "' 
            WHERE name IN ('include_customers_only', 'include_prospects_only', 'include_prospects_and_customers')
        ");

        $this->addSql("
            UPDATE prospect_filter_rule 
            SET 
                value = '" . ProspectFilterRuleRegistry::EXCLUDE_CLUB_MEMBERS_VALUE . "',
                description = '" . ProspectFilterRuleRegistry::EXCLUDE_CLUB_MEMBERS_RULE_DESCRIPTION . "'
            WHERE name = 'exclude_club_members'
        ");

        $this->addSql("
            UPDATE prospect_filter_rule 
            SET name = '" . ProspectFilterRuleRegistry::CLUB_MEMBERS_INCLUSION_RULE_NAME . "' 
            WHERE name = 'exclude_club_members'
        ");

        $this->addSql("
            UPDATE prospect_filter_rule 
            SET name = '" . ProspectFilterRuleRegistry::CUSTOMER_MAX_LTV_RULE_NAME . "' 
            WHERE name = 'exclude_ltv_greater_5000'
        ");

        $this->addSql("
            UPDATE prospect_filter_rule 
            SET 
                value = '" . ProspectFilterRuleRegistry::EXCLUDE_CUSTOMER_INSTALLATIONS_VALUE . "',
                description = '" . ProspectFilterRuleRegistry::EXCLUDE_CUSTOMER_INSTALLATIONS_DESCRIPTION . "',
                displayed_name = '" . ProspectFilterRuleRegistry::EXCLUDE_CUSTOMER_INSTALLATIONS_DISPLAYED_NAME . "'
            WHERE name = 'exclude_installations'
        ");

        $this->addSql("
            UPDATE prospect_filter_rule 
            SET name = '" . ProspectFilterRuleRegistry::CUSTOMER_INSTALLATIONS_INCLUSION_RULE_NAME . "' 
            WHERE name = 'exclude_installations'
        ");

        $this->addSql("
            INSERT INTO prospect_filter_rule (name, displayed_name, value, description) 
            SELECT 
                '" . ProspectFilterRuleRegistry::CLUB_MEMBERS_INCLUSION_RULE_NAME . "',
                '" . ProspectFilterRuleRegistry::INCLUDE_CLUB_MEMBERS_ONLY_DISPLAYED_NAME . "',
                '" . ProspectFilterRuleRegistry::INCLUDE_CLUB_MEMBERS_ONLY_VALUE . "',
                '" . ProspectFilterRuleRegistry::INCLUDE_CLUB_MEMBERS_ONLY_RULE_DESCRIPTION . "'
            WHERE NOT EXISTS (
                SELECT 1 FROM prospect_filter_rule 
                WHERE name = '" . ProspectFilterRuleRegistry::CLUB_MEMBERS_INCLUSION_RULE_NAME . "'
                AND value = '" . ProspectFilterRuleRegistry::INCLUDE_CLUB_MEMBERS_ONLY_VALUE . "'
            )
        ");

        $this->addSql("
            INSERT INTO prospect_filter_rule (name, displayed_name, value, description) 
            SELECT 
                '" . ProspectFilterRuleRegistry::CUSTOMER_INSTALLATIONS_INCLUSION_RULE_NAME . "',
                '" . ProspectFilterRuleRegistry::INCLUDE_CUSTOMER_INSTALLATIONS_ONLY_DISPLAYED_NAME . "',
                '" . ProspectFilterRuleRegistry::INCLUDE_CUSTOMER_INSTALLATIONS_ONLY_VALUE . "',
                '" . ProspectFilterRuleRegistry::INCLUDE_CUSTOMER_INSTALLATIONS_ONLY_DESCRIPTION . "'
            WHERE NOT EXISTS (
                SELECT 1 FROM prospect_filter_rule 
                WHERE name = '" . ProspectFilterRuleRegistry::CUSTOMER_INSTALLATIONS_INCLUSION_RULE_NAME . "'
                AND value = '" . ProspectFilterRuleRegistry::INCLUDE_CUSTOMER_INSTALLATIONS_ONLY_VALUE . "'
            )
        ");

        $this->addSql('
            ALTER TABLE prospect_filter_rule 
            DROP COLUMN IF EXISTS operator
        ');

        $this->addSql('
            CREATE UNIQUE INDEX IF NOT EXISTS name_value_uniq
            ON prospect_filter_rule (name, value)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS name_value_uniq');
    }
}

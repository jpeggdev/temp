<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250127233439 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            UPDATE prospect_filter_rule
            SET value = '" . ProspectFilterRuleRegistry::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_VALUE . "'
            WHERE displayed_name = '" . ProspectFilterRuleRegistry::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_DISPLAYED_NAME . "'
        ");

        $this->addSql("
            UPDATE prospect_filter_rule
            SET value = '" . ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_ONLY_VALUE . "'
            WHERE displayed_name = '" . ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_ONLY_RULE_DISPLAYED_NAME . "'
        ");

        $this->addSql("
            UPDATE prospect_filter_rule
            SET value = '" . ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_AND_CUSTOMERS_VALUE . "'
            WHERE displayed_name = '" . ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_AND_CUSTOMERS_RULE_DISPLAYED_NAME . "'
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql(sprintf(
            "UPDATE prospect_filter_rule
            SET value = NULL
            WHERE value IN ('%s', '%s', '%s')",
            ProspectFilterRuleRegistry::INCLUDE_ACTIVE_CUSTOMERS_ONLY_RULE_VALUE,
            ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_ONLY_VALUE,
            ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_AND_CUSTOMERS_VALUE
        ));
    }

}

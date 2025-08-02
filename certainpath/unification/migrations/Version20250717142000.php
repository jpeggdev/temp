<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250717142000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $addressTypeRules = array_filter(
            ProspectFilterRuleRegistry::getStaticRuleDefinitions(),
            static function ($rule) {
                return $rule['name'] === ProspectFilterRuleRegistry::ADDRESS_TYPE_INCLUSION_RULE_NAME;
            }
        );

        foreach ($addressTypeRules as $ruleDefinition) {
            $this->addSql("
                INSERT INTO prospect_filter_rule (
                    name,
                    displayed_name,
                    value,
                    description
                ) 
                SELECT :name, :displayed_name, :value, :description
                WHERE NOT EXISTS (
                    SELECT 1 FROM prospect_filter_rule 
                    WHERE name = :name AND value = :value
                )",
                [
                    'name' => $ruleDefinition['name'],
                    'displayed_name' => $ruleDefinition['displayedName'],
                    'value' => $ruleDefinition['value'],
                    'description' => $ruleDefinition['description']
                ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DELETE FROM prospect_filter_rule 
            WHERE name = :name",
            ['name' => ProspectFilterRuleRegistry::ADDRESS_TYPE_INCLUSION_RULE_NAME]
        );
    }
}

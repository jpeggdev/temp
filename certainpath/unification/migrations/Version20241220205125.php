<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241220205125 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE prospect_filter_rule (
                id SERIAL NOT NULL,
                name TEXT NOT NULL,
                displayed_name TEXT NOT NULL,
                value TEXT DEFAULT NULL,
                description TEXT DEFAULT NULL,
                PRIMARY KEY(id)
        )');


        foreach (ProspectFilterRuleRegistry::getStaticRuleDefinitions() as $ruleDefinition) {
            $this->addSql(sprintf("
                INSERT INTO prospect_filter_rule (
                    name,
                    displayed_name,
                    value,
                    description
                ) VALUES ('%s', '%s', '%s', '%s')",
                    $ruleDefinition['name'],
                    $ruleDefinition['displayedName'],
                    $ruleDefinition['value'],
                    $ruleDefinition['description'])
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE prospect_filter_rule');
    }
}

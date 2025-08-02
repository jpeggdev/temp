<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250110160541 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE campaign_prospect_filter_rule (
                campaign_id INT NOT NULL,
                prospect_filter_rule_id INT NOT NULL,
                PRIMARY KEY(campaign_id, prospect_filter_rule_id)
            )
        ');

        $this->addSql('
            CREATE INDEX IDX_82E984B9F639F774 ON campaign_prospect_filter_rule (campaign_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_82E984B9667FA559 ON campaign_prospect_filter_rule (prospect_filter_rule_id)
        ');
        $this->addSql('
            ALTER TABLE campaign_prospect_filter_rule
            ADD CONSTRAINT FK_82E984B9F639F774 
            FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE campaign_prospect_filter_rule
            ADD CONSTRAINT FK_82E984B9667FA559
            FOREIGN KEY (prospect_filter_rule_id) 
            REFERENCES prospect_filter_rule (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campaign_prospect_filter_rule DROP CONSTRAINT FK_82E984B9F639F774');
        $this->addSql('ALTER TABLE campaign_prospect_filter_rule DROP CONSTRAINT FK_82E984B9667FA559');
        $this->addSql('DROP TABLE campaign_prospect_filter_rule');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250213200655 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE INDEX campaign_event_campaign_identifier_idx ON campaign_event (campaign_identifier)
        ');

        $this->addSql('
            ALTER INDEX IF EXISTS company_job_event_campaign_identifier_idx 
            RENAME TO company_job_event_job_name_idx;
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DROP INDEX campaign_event_campaign_identifier_idx
        ');

        $this->addSql('
            ALTER INDEX IF EXISTS company_job_event_job_name_idx 
            RENAME TO company_job_event_campaign_identifier_idx;
        ');
    }
}

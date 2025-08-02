<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250206232604 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        /**
         * UPDATE the campaign_event table
         */
        $this->addSql('
            ALTER TABLE IF EXISTS campaign_event RENAME TO company_job_event
        ');
        $this->addSql('
            ALTER TABLE IF EXISTS company_job_event 
            RENAME COLUMN campaign_identifier TO job_name;
        ');
        $this->addSql('
            ALTER TABLE IF EXISTS company_job_event 
            RENAME COLUMN campaign_event_status_id TO event_status_id;
        ');
        $this->addSql('
            ALTER INDEX IF EXISTS campaign_event_campaign_identifier_idx 
            RENAME TO company_job_event_campaign_identifier_idx;
        ');
        $this->addSql('
            ALTER INDEX IF EXISTS campaign_event_pkey 
            RENAME TO company_job_event_pkey;
        ');
        $this->addSql('
            ALTER INDEX IF EXISTS campaign_event_created_at_idx 
            RENAME TO company_job_event_created_at_idx;
        ');
        $this->addSql('
            ALTER SEQUENCE IF EXISTS campaign_event_id_seq 
            RENAME TO company_job_event_id_seq;
        ');

        /**
         * UPDATE the campaign_event_status table
         */
        $this->addSql('
            ALTER TABLE IF EXISTS campaign_event_status 
            RENAME TO event_status;   
        ');
        $this->addSql('
            ALTER INDEX IF EXISTS campaign_event_status_pkey 
            RENAME TO event_status_pkey;
        ');
        $this->addSql('
            ALTER INDEX IF EXISTS idx_75ab6ec890d5b195
            RENAME TO IDX_9E93D27ED623E80
        ');
        $this->addSql('
            ALTER SEQUENCE IF EXISTS campaign_event_status_id_seq 
            RENAME TO event_status_id_seq;
        ');
    }

    public function down(Schema $schema): void
    {
        /**
         * Revert changes to campaign_event table
         */
        $this->addSql('
            ALTER TABLE IF EXISTS company_job_event 
            RENAME TO campaign_event;
        ');
        $this->addSql('
            ALTER TABLE IF EXISTS campaign_event 
            RENAME COLUMN job_name TO campaign_identifier;
        ');
        $this->addSql('
            ALTER TABLE IF EXISTS campaign_event 
            RENAME COLUMN event_status_id TO campaign_event_status_id;
        ');
        $this->addSql('
            ALTER INDEX IF EXISTS company_job_event_campaign_identifier_idx 
            RENAME TO campaign_event_campaign_identifier_idx;
        ');
        $this->addSql('
            ALTER INDEX IF EXISTS company_job_event_pkey 
            RENAME TO campaign_event_pkey;
        ');
        $this->addSql('
            ALTER INDEX IF EXISTS company_job_created_at_idx 
            RENAME TO campaign_event_created_at_idx;
        ');
        $this->addSql('
            ALTER SEQUENCE IF EXISTS company_job_event_id_seq 
            RENAME TO campaign_event_id_seq;
        ');

        /**
         * Revert changes to campaign_event_status table
         */
        $this->addSql('
            ALTER TABLE IF EXISTS event_status
            RENAME TO campaign_event_status;
        ');
        $this->addSql('
            ALTER INDEX IF EXISTS event_status_pkey
            RENAME TO campaign_event_status_pkey;
        ');
        $this->addSql('
            ALTER INDEX IF EXISTS idx_9e93d27ed623e80 RENAME TO idx_75ab6ec890d5b195
        ');
        $this->addSql('
            ALTER SEQUENCE IF EXISTS event_status_id_seq 
            RENAME TO campaign_event_status_id_seq;
        ');
    }
}

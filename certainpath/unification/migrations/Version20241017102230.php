<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\BatchStatus;
use App\Entity\CampaignIterationStatus;
use App\Entity\CampaignStatus;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241017102230 extends AbstractMigration
{
    private array $campaignStatuses = [
        CampaignStatus::STATUS_ACTIVE,
        CampaignStatus::STATUS_PAUSED,
        CampaignStatus::STATUS_COMPLETED,
    ];

    private array $campaignIterationStatuses = [
        CampaignIterationStatus::STATUS_ACTIVE,
        CampaignIterationStatus::STATUS_COMPLETED,
    ];

    private array $batchStatuses = [
        BatchStatus::STATUS_NEW,
        BatchStatus::STATUS_UPLOADED,
        BatchStatus::STATUS_PROCESSED,
    ];

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mail_package DROP COLUMN prospect_id');
        $this->addSql('ALTER TABLE mail_package DROP COLUMN mail_date');

        // Batch Status table
        $this->addSql('CREATE TABLE batch_status (
            id SERIAL NOT NULL PRIMARY KEY,
            name TEXT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        )');
        $this->addSql('COMMENT ON COLUMN batch_status.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN batch_status.updated_at IS \'(DC2Type:datetime_immutable)\'');

        // Batch table
        $this->addSql('CREATE TABLE batch (
            id SERIAL NOT NULL PRIMARY KEY,
            campaign_id INT NOT NULL,
            campaign_iteration_id INT NOT NULL,
            batch_status_id INT NOT NULL,
            name TEXT NOT NULL,
            description TEXT DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        )');
        $this->addSql('CREATE INDEX IDX_F80B52D4F639F774 ON batch (campaign_id)');
        $this->addSql('CREATE INDEX IDX_F30F62A424CE2F5C ON batch (batch_status_id)');
        $this->addSql('COMMENT ON COLUMN batch.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN batch.updated_at IS \'(DC2Type:datetime_immutable)\'');

        // Batch Iteration Status table
        $this->addSql('CREATE TABLE campaign_iteration_status (
            id SERIAL NOT NULL PRIMARY KEY,
            name TEXT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        )');
        $this->addSql('COMMENT ON COLUMN campaign_iteration_status.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN campaign_iteration_status.updated_at IS \'(DC2Type:datetime_immutable)\'');

        $this->addSql('CREATE TABLE campaign_iteration_week (
            id BIGSERIAL PRIMARY KEY, 
            campaign_iteration_id INT NOT NULL,
            week_number INT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
            
        )');
        $this->addSql('COMMENT ON COLUMN campaign_iteration_status.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN campaign_iteration_status.updated_at IS \'(DC2Type:datetime_immutable)\'');

        // Batch Prospect table
        $this->addSql('CREATE TABLE batch_prospect (
            batch_id BIGINT NOT NULL,
            prospect_id BIGINT NOT NULL,
            PRIMARY KEY(batch_id, prospect_id)
        )');
        $this->addSql('CREATE INDEX IDX_4220E21DF39EBE7A ON batch_prospect (batch_id)');
        $this->addSql('CREATE INDEX IDX_4220E21DD182060A ON batch_prospect (prospect_id)');
        $this->addSql('ALTER TABLE batch_prospect ADD CONSTRAINT FK_4220E21DF39EBE7A FOREIGN KEY (batch_id) REFERENCES batch (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE batch_prospect ADD CONSTRAINT FK_4220E21DD182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F80B52D446F832DF ON batch (campaign_iteration_id)');

        // Campaign table
        $this->addSql('CREATE TABLE campaign (
            id SERIAL NOT NULL PRIMARY KEY,
            company_id INT NOT NULL,
            campaign_status_id INT NOT NULL,
            name TEXT NOT NULL,
            description TEXT DEFAULT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            mailing_frequency_weeks INT NOT NULL,
            phone_number TEXT DEFAULT NULL,
            deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        )');
        $this->addSql('CREATE INDEX IDX_1F1512DD979B1AD6 ON campaign (company_id)');
        $this->addSql('CREATE INDEX IDX_1F1512DDAB7F1CC6 ON campaign (campaign_status_id)');
        $this->addSql('COMMENT ON COLUMN campaign.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN campaign.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN campaign.updated_at IS \'(DC2Type:datetime_immutable)\'');

        // Campaign Status table
        $this->addSql('CREATE TABLE campaign_status (
            id SERIAL NOT NULL,
            name TEXT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('COMMENT ON COLUMN campaign_status.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN campaign_status.updated_at IS \'(DC2Type:datetime_immutable)\'');

        // Campaign Mail Package table
        $this->addSql('CREATE TABLE campaign_mail_package (
            campaign_id INT NOT NULL,
            mail_package_id INT NOT NULL,
            PRIMARY KEY(campaign_id, mail_package_id)
        )');
        $this->addSql('CREATE INDEX IDX_596537C4F639F774 ON campaign_mail_package (campaign_id)');
        $this->addSql('CREATE INDEX IDX_596537C4208F4103 ON campaign_mail_package (mail_package_id)');

        // Campaign Iteration table
        $this->addSql('CREATE TABLE campaign_iteration (
            id SERIAL NOT NULL PRIMARY KEY,
            campaign_id INT NOT NULL,
            campaign_iteration_status_id INT NOT NULL,
            iteration_number INT NOT NULL,
            description TEXT DEFAULT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
        )');
        $this->addSql('CREATE INDEX IDX_2E59CD66F639F774 ON campaign_iteration (campaign_id)');
        $this->addSql('CREATE INDEX IDX_F80B52D426CE8F8B ON campaign_iteration (campaign_iteration_status_id)');
        $this->addSql('COMMENT ON COLUMN campaign_iteration.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN campaign_iteration.updated_at IS \'(DC2Type:datetime_immutable)\'');

        // Campaign Tracker table
        $this->addSql('CREATE TABLE campaign_tracker (
            id SERIAL NOT NULL PRIMARY KEY,
            campaign_id INT NOT NULL,
            campaign_iteration_id INT NOT NULL,
            campaign_iteration_week_id BIGINT NOT NULL,
            batch_id INT NOT NULL,
            mail_package_id INT NOT NULL
        )');
        $this->addSql('CREATE INDEX IDX_4D9B683F639F774 ON campaign_tracker (campaign_id)');
        $this->addSql('CREATE INDEX IDX_4D9B68346F832DF ON campaign_tracker (campaign_iteration_id)');
        $this->addSql('CREATE INDEX IDX_4D9B683208F4103 ON campaign_tracker (mail_package_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4D9B683F39EBE7A ON campaign_tracker (batch_id)');

        // Foreign key constraints
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT FK_F80B52D4F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT FK_F80B52D446F832DF FOREIGN KEY (campaign_iteration_id) REFERENCES campaign_iteration (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DD979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DDAB7F1CC6 FOREIGN KEY (campaign_status_id) REFERENCES campaign_status (id)');
        $this->addSql('ALTER TABLE campaign_mail_package ADD CONSTRAINT FK_596537C4F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE campaign_mail_package ADD CONSTRAINT FK_596537C4208F4103 FOREIGN KEY (mail_package_id) REFERENCES mail_package (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE campaign_iteration ADD CONSTRAINT FK_2E59CD66F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)');
        $this->addSql('ALTER TABLE campaign_iteration ADD CONSTRAINT FK_F80B52D426CE8F8B FOREIGN KEY (campaign_iteration_status_id) REFERENCES campaign_iteration_status (id)');
        $this->addSql('ALTER TABLE campaign_tracker ADD CONSTRAINT FK_4D9B683F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)');
        $this->addSql('ALTER TABLE campaign_tracker ADD CONSTRAINT FK_4D9B683208F4103 FOREIGN KEY (mail_package_id) REFERENCES mail_package (id)');
        $this->addSql('ALTER TABLE campaign_tracker ADD CONSTRAINT FK_4D9B68346F832DF FOREIGN KEY (campaign_iteration_id) REFERENCES campaign_iteration (id)');
        $this->addSql('ALTER TABLE campaign_tracker ADD CONSTRAINT FK_4D9B6835219457F FOREIGN KEY (campaign_iteration_week_id) REFERENCES campaign_iteration_week (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        // Populate tables with initial statuses
        foreach ($this->campaignStatuses as $campaignStatus) {
            $this->addSql('INSERT INTO campaign_status (name, created_at, updated_at) VALUES (?, NOW(), NOW())', [$campaignStatus]);
        }

        foreach ($this->campaignIterationStatuses as $batchQueryStatus) {
            $this->addSql('INSERT INTO campaign_iteration_status (name, created_at, updated_at) VALUES (?, NOW(), NOW())', [$batchQueryStatus]);
        }

        foreach ($this->batchStatuses as $batchStatus) {
            $this->addSql('INSERT INTO batch_status (name, created_at, updated_at) VALUES (?, NOW(), NOW())', [$batchStatus]);
        }
    }

    public function down(Schema $schema): void
    {
        // Drop all created tables
        $this->addSql('DROP TABLE IF EXISTS batch_status CASCADE');
        $this->addSql('DROP TABLE IF EXISTS batch CASCADE');
        $this->addSql('DROP TABLE IF EXISTS campaign_iteration_status CASCADE');
        $this->addSql('DROP TABLE IF EXISTS batch_prospect CASCADE');
        $this->addSql('DROP TABLE IF EXISTS campaign CASCADE');
        $this->addSql('DROP TABLE IF EXISTS campaign_mail_package CASCADE');
        $this->addSql('DROP TABLE IF EXISTS campaign_iteration CASCADE');
        $this->addSql('DROP TABLE IF EXISTS campaign_status CASCADE');
        $this->addSql('DROP TABLE IF EXISTS campaign_iteration_week CASCADE');
        $this->addSql('DROP TABLE IF EXISTS campaign_tracker CASCADE');

        // Drop related sequences to clean up state
        $this->addSql('DROP SEQUENCE IF EXISTS batch_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS batch_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS campaign_iteration_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS campaign_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS campaign_iteration_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS campaign_iteration_week_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS campaign_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS campaign_tracker_id_seq CASCADE');

        // Restore the dropped columns in the mail_package table
        $this->addSql('ALTER TABLE mail_package ADD IF NOT EXISTS prospect_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mail_package ADD IF NOT EXISTS mail_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        // Restore the foreign key constraint for prospect_id
        $this->addSql('ALTER TABLE mail_package ADD CONSTRAINT FK_4D5E6FAD182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

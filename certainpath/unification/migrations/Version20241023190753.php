<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241023190753 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER INDEX idx_f80b52d426ce8f8b RENAME TO IDX_2E59CD6666F19BA4');
        $this->addSql('ALTER INDEX idx_f30f62a424ce2f5c RENAME TO IDX_F80B52D426CE8F8B');

        $this->addSql('ALTER TABLE batch DROP CONSTRAINT FK_F80B52D4F639F774');
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT FK_F80B52D426CE8F8B FOREIGN KEY (batch_status_id) REFERENCES batch_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT FK_F80B52D4F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE campaign_iteration DROP description');

        $this->addSql('ALTER TABLE campaign_iteration_week ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE campaign_iteration_week ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN campaign_iteration_week.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN campaign_iteration_week.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE campaign_iteration_week ADD CONSTRAINT FK_FC40B1FE46F832DF FOREIGN KEY (campaign_iteration_id) REFERENCES campaign_iteration (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FC40B1FE46F832DF ON campaign_iteration_week (campaign_iteration_id)');

        $this->addSql('ALTER TABLE campaign_tracker ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE campaign_tracker ADD CONSTRAINT FK_4D9B683F39EBE7A FOREIGN KEY (batch_id) REFERENCES batch (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE batch_prospect DROP CONSTRAINT batch_prospect_pkey');
        $this->addSql('ALTER TABLE batch_prospect ALTER batch_id TYPE INT');
        $this->addSql('ALTER TABLE batch_prospect ALTER prospect_id TYPE INT');
        $this->addSql('ALTER TABLE batch_prospect ADD PRIMARY KEY (prospect_id, batch_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campaign_iteration_week DROP CONSTRAINT FK_FC40B1FE46F832DF');
        $this->addSql('DROP INDEX IDX_FC40B1FE46F832DF');
        $this->addSql('ALTER TABLE campaign_iteration_week ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE campaign_iteration_week ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');

        $this->addSql('ALTER TABLE campaign_tracker DROP CONSTRAINT FK_4D9B683F39EBE7A');
        $this->addSql('ALTER TABLE campaign_tracker ALTER id TYPE INT');

        $this->addSql('ALTER TABLE campaign_iteration ADD description TEXT DEFAULT NULL');

        $this->addSql('ALTER TABLE batch_prospect DROP CONSTRAINT batch_prospect_pkey');
        $this->addSql('ALTER TABLE batch_prospect ALTER prospect_id TYPE BIGINT');
        $this->addSql('ALTER TABLE batch_prospect ALTER batch_id TYPE BIGINT');
        $this->addSql('ALTER TABLE batch_prospect ADD PRIMARY KEY (batch_id, prospect_id)');

        $this->addSql('ALTER TABLE batch DROP CONSTRAINT FK_F80B52D426CE8F8B');
        $this->addSql('ALTER TABLE batch DROP CONSTRAINT fk_f80b52d4f639f774');
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT fk_f80b52d4f639f774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Revert the index renames
        $this->addSql('ALTER INDEX IDX_F80B52D426CE8F8B RENAME TO idx_f30f62a424ce2f5c');
        $this->addSql('ALTER INDEX IDX_2E59CD6666F19BA4 RENAME TO idx_f80b52d426ce8f8b');
    }
}

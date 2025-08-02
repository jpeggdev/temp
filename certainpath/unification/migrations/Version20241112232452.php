<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241112232452 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Drop the campaign_tracker tables
        $this->addSql('ALTER TABLE campaign_mail_package DROP CONSTRAINT fk_596537c4f639f774');
        $this->addSql('ALTER TABLE campaign_mail_package DROP CONSTRAINT fk_596537c4208f4103');
        $this->addSql('ALTER TABLE campaign_tracker DROP CONSTRAINT fk_4d9b683f639f774');
        $this->addSql('ALTER TABLE campaign_tracker DROP CONSTRAINT fk_4d9b683208f4103');
        $this->addSql('ALTER TABLE campaign_tracker DROP CONSTRAINT fk_4d9b68346f832df');
        $this->addSql('ALTER TABLE campaign_tracker DROP CONSTRAINT fk_4d9b6835219457f');
        $this->addSql('ALTER TABLE campaign_tracker DROP CONSTRAINT fk_4d9b683f39ebe7a');
        $this->addSql('DROP TABLE campaign_tracker');
        $this->addSql('DROP SEQUENCE IF EXISTS campaign_tracker_id_seq CASCADE');

        // Drop the campaign_mail_package
        $this->addSql('DROP TABLE campaign_mail_package');

        // Alter the batch table
        $this->addSql('ALTER TABLE batch ADD campaign_iteration_week_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE batch ADD mail_package_id INT NOT NULL');
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT FK_F80B52D45219457F FOREIGN KEY (campaign_iteration_week_id) REFERENCES campaign_iteration_week (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT FK_F80B52D4208F4103 FOREIGN KEY (mail_package_id) REFERENCES mail_package (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F80B52D45219457F ON batch (campaign_iteration_week_id)');
        $this->addSql('CREATE INDEX IDX_F80B52D4208F4103 ON batch (mail_package_id)');
        $this->addSql('ALTER TABLE campaign ADD mail_package_id INT NOT NULL');
        $this->addSql('ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DD208F4103 FOREIGN KEY (mail_package_id) REFERENCES mail_package (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1F1512DD208F4103 ON campaign (mail_package_id)');
    }

    public function down(Schema $schema): void
    {
        // Recreate the campaign_mail_package
        $this->addSql('CREATE SEQUENCE campaign_tracker_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE campaign_mail_package (
            campaign_id INT NOT NULL,
            mail_package_id INT NOT NULL,
            PRIMARY KEY(campaign_id, mail_package_id))
        ');
        $this->addSql('CREATE INDEX idx_596537c4208f4103 ON campaign_mail_package (mail_package_id)');
        $this->addSql('CREATE INDEX idx_596537c4f639f774 ON campaign_mail_package (campaign_id)');
        $this->addSql('ALTER TABLE campaign_mail_package ADD CONSTRAINT fk_596537c4f639f774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE campaign_mail_package ADD CONSTRAINT fk_596537c4208f4103 FOREIGN KEY (mail_package_id) REFERENCES mail_package (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Recreate the campaign_tracker table
        $this->addSql('CREATE TABLE campaign_tracker (
            id BIGSERIAL NOT NULL,
            campaign_id INT NOT NULL,
            campaign_iteration_id INT NOT NULL,
            campaign_iteration_week_id BIGINT NOT NULL,
            batch_id INT NOT NULL,
            mail_package_id INT NOT NULL,
            PRIMARY KEY(id))
        ');
        $this->addSql('CREATE UNIQUE INDEX uniq_4d9b683f39ebe7a ON campaign_tracker (batch_id)');
        $this->addSql('CREATE INDEX idx_4d9b683208f4103 ON campaign_tracker (mail_package_id)');
        $this->addSql('CREATE INDEX idx_4d9b68346f832df ON campaign_tracker (campaign_iteration_id)');
        $this->addSql('CREATE INDEX idx_4d9b683f639f774 ON campaign_tracker (campaign_id)');
        $this->addSql('CREATE INDEX IDX_4D9B6835219457F ON campaign_tracker (campaign_iteration_week_id)');
        $this->addSql('ALTER TABLE campaign_tracker ADD CONSTRAINT fk_4d9b683f639f774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE campaign_tracker ADD CONSTRAINT fk_4d9b683208f4103 FOREIGN KEY (mail_package_id) REFERENCES mail_package (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE campaign_tracker ADD CONSTRAINT fk_4d9b68346f832df FOREIGN KEY (campaign_iteration_id) REFERENCES campaign_iteration (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE campaign_tracker ADD CONSTRAINT fk_4d9b6835219457f FOREIGN KEY (campaign_iteration_week_id) REFERENCES campaign_iteration_week (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE campaign_tracker ADD CONSTRAINT fk_4d9b683f39ebe7a FOREIGN KEY (batch_id) REFERENCES batch (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Revert changes made to the campaign table
        $this->addSql('ALTER TABLE campaign DROP CONSTRAINT FK_1F1512DD208F4103');
        $this->addSql('DROP INDEX IDX_1F1512DD208F4103');
        $this->addSql('ALTER TABLE campaign DROP mail_package_id');

        // Revert changes made to the batch table
        $this->addSql('ALTER TABLE batch DROP CONSTRAINT FK_F80B52D45219457F');
        $this->addSql('ALTER TABLE batch DROP CONSTRAINT FK_F80B52D4208F4103');
        $this->addSql('DROP INDEX IDX_F80B52D45219457F');
        $this->addSql('DROP INDEX IDX_F80B52D4208F4103');
        $this->addSql('ALTER TABLE batch DROP campaign_iteration_week_id');
        $this->addSql('ALTER TABLE batch DROP mail_package_id');
    }
}

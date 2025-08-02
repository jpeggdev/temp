<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241118230333 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE batch DROP CONSTRAINT IF EXISTS fk_f80b52d4208f4103');
        $this->addSql('ALTER TABLE batch DROP CONSTRAINT IF EXISTS FK_F80B52D4F639F774');
        $this->addSql('DROP INDEX IF EXISTS idx_f80b52d4208f4103');
        $this->addSql('ALTER TABLE batch DROP IF EXISTS mail_package_id');
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT FK_F80B52D4F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('DROP INDEX IF EXISTS idx_1f1512dd208f4103');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F1512DD208F4103 ON campaign (mail_package_id)');

        $this->addSql('ALTER TABLE campaign_file ADD mail_package_id INT NOT NULL');
        $this->addSql('ALTER TABLE campaign_file ADD CONSTRAINT FK_8D780DF2208F4103 FOREIGN KEY (mail_package_id) REFERENCES mail_package (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8D780DF2208F4103 ON campaign_file (mail_package_id)');

        $this->addSql('ALTER TABLE campaign_iteration DROP CONSTRAINT FK_2E59CD66F639F774');
        $this->addSql('ALTER TABLE campaign_iteration ADD CONSTRAINT FK_2E59CD66F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE campaign_iteration_week DROP CONSTRAINT FK_FC40B1FE46F832DF');
        $this->addSql('ALTER TABLE campaign_iteration_week ADD CONSTRAINT FK_FC40B1FE46F832DF FOREIGN KEY (campaign_iteration_id) REFERENCES campaign_iteration (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campaign_iteration_week DROP CONSTRAINT fk_fc40b1fe46f832df');
        $this->addSql('ALTER TABLE campaign_iteration_week ADD CONSTRAINT fk_fc40b1fe46f832df FOREIGN KEY (campaign_iteration_id) REFERENCES campaign_iteration (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('DROP INDEX UNIQ_1F1512DD208F4103');
        $this->addSql('CREATE INDEX idx_1f1512dd208f4103 ON campaign (mail_package_id)');

        $this->addSql('ALTER TABLE campaign_iteration DROP CONSTRAINT fk_2e59cd66f639f774');
        $this->addSql('ALTER TABLE campaign_iteration ADD CONSTRAINT fk_2e59cd66f639f774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE batch DROP CONSTRAINT fk_f80b52d4f639f774');
        $this->addSql('ALTER TABLE batch ADD mail_package_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT fk_f80b52d4208f4103 FOREIGN KEY (mail_package_id) REFERENCES mail_package (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE batch ADD CONSTRAINT fk_f80b52d4f639f774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_f80b52d4208f4103 ON batch (mail_package_id)');

        $this->addSql('ALTER TABLE campaign_file DROP CONSTRAINT FK_8D780DF2208F4103');
        $this->addSql('DROP INDEX IDX_8D780DF2208F4103');
        $this->addSql('ALTER TABLE campaign_file DROP mail_package_id');
    }
}

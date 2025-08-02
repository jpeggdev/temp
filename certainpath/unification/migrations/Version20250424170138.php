<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250424170138 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->alterInvoiceTable();
        $this->alterLocationTable();
        $this->createCampaignLocationTable();
    }

    public function down(Schema $schema): void
    {
        $this->revertLocationTable();
        $this->revertInvoiceTable();
        $this->dropCampaignLocationTable();
    }

    private function alterInvoiceTable(): void
    {
        $this->addSql('
            ALTER INDEX IF EXISTS idx_f2f327ff26ce8f8b
            RENAME TO IDX_420A01426CE8F8B
        ');
        $this->addSql('
            ALTER TABLE invoice
            DROP CONSTRAINT IF EXISTS fk_9065174464d218e
        ');
        $this->addSql('DROP INDEX IF EXISTS idx_9065174464d218e');
        $this->addSql('
            ALTER TABLE invoice
            DROP COLUMN IF EXISTS location_id
        ');
    }

    private function revertInvoiceTable(): void
    {
        $this->addSql('
            ALTER INDEX IF EXISTS idx_420a01426ce8f8b
            RENAME TO idx_f2f327ff26ce8f8b
        ');
        $this->addSql('
            ALTER TABLE invoice
            ADD COLUMN location_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE invoice
            ADD CONSTRAINT fk_9065174464d218e
            FOREIGN KEY (location_id)
            REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');

        $this->addSql('CREATE INDEX idx_9065174464d218e ON invoice (location_id)');
    }

    private function alterLocationTable(): void
    {
        $this->addSql('
            ALTER TABLE location
            DROP CONSTRAINT IF EXISTS fk_5e9e89cb1a0c4e3a
        ');
        $this->addSql('
            ALTER TABLE location
            DROP CONSTRAINT IF EXISTS fk_5e9e89cb6646778d
        ');
        $this->addSql('
            ALTER TABLE location
            DROP CONSTRAINT IF EXISTS fk_5e9e89cb894dac38
        ');
        $this->addSql('
            ALTER TABLE location
            DROP CONSTRAINT IF EXISTS fk_5e9e89cba58ecb40
        ');
        $this->addSql('DROP INDEX IF EXISTS location_external_id_idx');
        $this->addSql('DROP INDEX IF EXISTS location_company_external_id_idx');
        $this->addSql('DROP INDEX IF EXISTS uniq_5e9e89cb894dac38');
        $this->addSql('DROP INDEX IF EXISTS uniq_5e9e89cb6646778d');
        $this->addSql('DROP INDEX IF EXISTS uniq_5e9e89cb1a0c4e3a');
        $this->addSql('DROP INDEX IF EXISTS idx_5e9e89cba58ecb40');
        $this->addSql('
            ALTER TABLE location
            DROP COLUMN IF EXISTS physical_address_id
        ');
        $this->addSql('
            ALTER TABLE location
            DROP COLUMN IF EXISTS primary_email_id
        ');
        $this->addSql('
            ALTER TABLE location
            DROP COLUMN IF EXISTS primary_phone_id
        ');
        $this->addSql('
            ALTER TABLE location
            DROP COLUMN IF EXISTS business_unit_id
        ');
        $this->addSql('
            ALTER TABLE location
            DROP COLUMN IF EXISTS external_id
        ');
        $this->addSql('
            ALTER TABLE location
            ADD postal_codes TEXT NOT NULL DEFAULT 0
        ');
        $this->addSql('
            COMMENT ON COLUMN location.postal_codes IS \'(DC2Type:simple_array)\'
        ');
    }

    private function revertLocationTable(): void
    {
        $this->addSql('
            ALTER TABLE location
            ADD COLUMN IF NOT EXISTS physical_address_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE location
            ADD COLUMN IF NOT EXISTS primary_email_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE location
            ADD COLUMN IF NOT EXISTS primary_phone_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE location
            ADD COLUMN IF NOT EXISTS business_unit_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE location
            ADD external_id TEXT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE location
            DROP IF EXISTS postal_codes
        ');
        $this->addSql('
            ALTER TABLE location
            ADD CONSTRAINT fk_5e9e89cb1a0c4e3a
            FOREIGN KEY (primary_phone_id)
            REFERENCES phone (id)NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE location
            ADD CONSTRAINT fk_5e9e89cb6646778d
            FOREIGN KEY (physical_address_id)
            REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE location
            ADD CONSTRAINT fk_5e9e89cb894dac38
            FOREIGN KEY (primary_email_id)
            REFERENCES email (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE location
            ADD CONSTRAINT fk_5e9e89cba58ecb40
            FOREIGN KEY (business_unit_id)
            REFERENCES business_unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            CREATE INDEX location_external_id_idx
            ON location (external_id)
        ');
        $this->addSql('
            CREATE INDEX location_company_external_id_idx
            ON location (company_id, external_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX uniq_5e9e89cb894dac38
            ON location (primary_email_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX uniq_5e9e89cb6646778d
            ON location (physical_address_id)
        ');
        $this->addSql('
            CREATE UNIQUE INDEX uniq_5e9e89cb1a0c4e3a
            ON location (primary_phone_id)
        ');
        $this->addSql('
            CREATE INDEX idx_5e9e89cba58ecb40
            ON location (business_unit_id)
        ');
    }

    private function createCampaignLocationTable(): void
    {
        $this->addSql('
            CREATE TABLE campaign_location (
                campaign_id INT NOT NULL,
                location_id INT NOT NULL,
                PRIMARY KEY(campaign_id, location_id))
            ');
        $this->addSql('
            CREATE INDEX IDX_6CEE5FB4F639F774
            ON campaign_location (campaign_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_6CEE5FB464D218E
            ON campaign_location (location_id)
        ');
        $this->addSql('
            ALTER TABLE IF EXISTS campaign_location
            ADD CONSTRAINT FK_6CEE5FB4F639F774
            FOREIGN KEY (campaign_id)
            REFERENCES campaign (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE IF EXISTS campaign_location
            ADD CONSTRAINT FK_6CEE5FB464D218E
            FOREIGN KEY (location_id)
            REFERENCES location (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    private function dropCampaignLocationTable(): void
    {
        $this->addSql('
            ALTER TABLE IF EXISTS campaign_location
            DROP CONSTRAINT IF EXISTS FK_6CEE5FB4F639F774
        ');
        $this->addSql('
            ALTER TABLE IF EXISTS campaign_location
            DROP CONSTRAINT IF EXISTS FK_6CEE5FB464D218E
        ');
        $this->addSql('DROP TABLE IF EXISTS campaign_location');
    }
}

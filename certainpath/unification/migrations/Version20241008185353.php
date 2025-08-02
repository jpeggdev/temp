<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241008185353 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE setting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE prospect_address (prospect_id INT NOT NULL, address_id INT NOT NULL, PRIMARY KEY(prospect_id, address_id))');
        $this->addSql('CREATE INDEX IDX_CE027F55D182060A ON prospect_address (prospect_id)');
        $this->addSql('CREATE INDEX IDX_CE027F55F5B7AF75 ON prospect_address (address_id)');
        $this->addSql('CREATE TABLE setting (id INT NOT NULL, name TEXT NOT NULL, value TEXT NOT NULL, type TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE prospect_address ADD CONSTRAINT FK_CE027F55D182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE prospect_address ADD CONSTRAINT FK_CE027F55F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE prospect ADD processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN prospect.processed_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE setting_id_seq CASCADE');
        $this->addSql('ALTER TABLE prospect_address DROP CONSTRAINT FK_CE027F55D182060A');
        $this->addSql('ALTER TABLE prospect_address DROP CONSTRAINT FK_CE027F55F5B7AF75');
        $this->addSql('DROP TABLE prospect_address');
        $this->addSql('DROP TABLE setting');
        $this->addSql('ALTER TABLE prospect DROP processed_at');
    }
}

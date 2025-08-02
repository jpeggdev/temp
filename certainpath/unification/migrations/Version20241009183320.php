<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241009183320 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer_address (customer_id INT NOT NULL, address_id INT NOT NULL, PRIMARY KEY(customer_id, address_id))');
        $this->addSql('CREATE INDEX IDX_1193CB3F9395C3F3 ON customer_address (customer_id)');
        $this->addSql('CREATE INDEX IDX_1193CB3FF5B7AF75 ON customer_address (address_id)');
        $this->addSql('ALTER TABLE customer_address ADD CONSTRAINT FK_1193CB3F9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE customer_address ADD CONSTRAINT FK_1193CB3FF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT fk_81398e09d182060a');
        $this->addSql('DROP INDEX uniq_81398e09d182060a');
        $this->addSql('ALTER TABLE customer ADD processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE customer DROP prospect_id');
        $this->addSql('COMMENT ON COLUMN customer.processed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE prospect ADD customer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE prospect ADD CONSTRAINT FK_C9CE8C7D9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C9CE8C7D9395C3F3 ON prospect (customer_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer_address DROP CONSTRAINT FK_1193CB3F9395C3F3');
        $this->addSql('ALTER TABLE customer_address DROP CONSTRAINT FK_1193CB3FF5B7AF75');
        $this->addSql('DROP TABLE customer_address');
        $this->addSql('ALTER TABLE customer ADD prospect_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer DROP processed_at');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT fk_81398e09d182060a FOREIGN KEY (prospect_id) REFERENCES prospect (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_81398e09d182060a ON customer (prospect_id)');
        $this->addSql('ALTER TABLE prospect DROP CONSTRAINT FK_C9CE8C7D9395C3F3');
        $this->addSql('DROP INDEX UNIQ_C9CE8C7D9395C3F3');
        $this->addSql('ALTER TABLE prospect DROP customer_id');
    }
}

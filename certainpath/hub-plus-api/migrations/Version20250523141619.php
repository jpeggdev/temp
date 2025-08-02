<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250523141619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_discount_ledger ADD discount_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_discount_ledger ADD discount_type_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event_discount_ledger ADD intacct_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event_discount_ledger RENAME COLUMN type TO usage');
        $this->addSql('ALTER TABLE event_discount_ledger ADD CONSTRAINT FK_67E8ECB37344E182 FOREIGN KEY (discount_type_id) REFERENCES discount_type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_67E8ECB37344E182 ON event_discount_ledger (discount_type_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_discount_ledger DROP CONSTRAINT FK_67E8ECB37344E182');
        $this->addSql('DROP INDEX IDX_67E8ECB37344E182');
        $this->addSql('ALTER TABLE event_discount_ledger ADD type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event_discount_ledger DROP discount_type_id');
        $this->addSql('ALTER TABLE event_discount_ledger DROP usage');
        $this->addSql('ALTER TABLE event_discount_ledger DROP discount_type_name');
        $this->addSql('ALTER TABLE event_discount_ledger DROP intacct_id');
    }
}

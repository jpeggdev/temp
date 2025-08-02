<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507022057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_discount_ledger ADD company_id INT NOT NULL');
        $this->addSql('ALTER TABLE event_discount_ledger ADD created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE event_discount_ledger ADD updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN event_discount_ledger.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_discount_ledger.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE event_discount_ledger ADD CONSTRAINT FK_67E8ECB3979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_67E8ECB3979B1AD6 ON event_discount_ledger (company_id)');
        $this->addSql('ALTER TABLE event_voucher_ledger ADD created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE event_voucher_ledger ADD updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE event_voucher_ledger RENAME COLUMN seats_used TO company_id');
        $this->addSql('COMMENT ON COLUMN event_voucher_ledger.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_voucher_ledger.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE event_voucher_ledger ADD CONSTRAINT FK_DE9BB432979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_DE9BB432979B1AD6 ON event_voucher_ledger (company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_voucher_ledger DROP CONSTRAINT FK_DE9BB432979B1AD6');
        $this->addSql('DROP INDEX IDX_DE9BB432979B1AD6');
        $this->addSql('ALTER TABLE event_voucher_ledger DROP created_at');
        $this->addSql('ALTER TABLE event_voucher_ledger DROP updated_at');
        $this->addSql('ALTER TABLE event_voucher_ledger RENAME COLUMN company_id TO seats_used');
        $this->addSql('ALTER TABLE event_discount_ledger DROP CONSTRAINT FK_67E8ECB3979B1AD6');
        $this->addSql('DROP INDEX IDX_67E8ECB3979B1AD6');
        $this->addSql('ALTER TABLE event_discount_ledger DROP company_id');
        $this->addSql('ALTER TABLE event_discount_ledger DROP created_at');
        $this->addSql('ALTER TABLE event_discount_ledger DROP updated_at');
    }
}

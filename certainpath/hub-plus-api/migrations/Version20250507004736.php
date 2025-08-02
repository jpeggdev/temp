<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507004736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_session ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event_voucher_ledger ADD event_checkout_id INT NOT NULL');
        $this->addSql('ALTER TABLE event_voucher_ledger ADD CONSTRAINT FK_DE9BB432BFE454A4 FOREIGN KEY (event_checkout_id) REFERENCES event_checkout (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_DE9BB432BFE454A4 ON event_voucher_ledger (event_checkout_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_voucher_ledger DROP CONSTRAINT FK_DE9BB432BFE454A4');
        $this->addSql('DROP INDEX IDX_DE9BB432BFE454A4');
        $this->addSql('ALTER TABLE event_voucher_ledger DROP event_checkout_id');
        $this->addSql('ALTER TABLE event_session DROP name');
    }
}

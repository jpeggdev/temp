<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521224421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ADD is_voucher_eligible BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE event_session ADD venue_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_session ADD timezone_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_session ADD is_virtual_only BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE event_session ADD CONSTRAINT FK_55137C7B40A73EBA FOREIGN KEY (venue_id) REFERENCES event_venue (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_session ADD CONSTRAINT FK_55137C7B3FE997DE FOREIGN KEY (timezone_id) REFERENCES timezone (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_55137C7B40A73EBA ON event_session (venue_id)');
        $this->addSql('CREATE INDEX IDX_55137C7B3FE997DE ON event_session (timezone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP is_voucher_eligible');
        $this->addSql('ALTER TABLE event_session DROP CONSTRAINT FK_55137C7B40A73EBA');
        $this->addSql('ALTER TABLE event_session DROP CONSTRAINT FK_55137C7B3FE997DE');
        $this->addSql('DROP INDEX IDX_55137C7B40A73EBA');
        $this->addSql('DROP INDEX IDX_55137C7B3FE997DE');
        $this->addSql('ALTER TABLE event_session DROP venue_id');
        $this->addSql('ALTER TABLE event_session DROP timezone_id');
        $this->addSql('ALTER TABLE event_session DROP is_virtual_only');
    }
}

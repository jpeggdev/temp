<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250419155320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ALTER is_published SET DEFAULT false');
        $this->addSql('ALTER TABLE event ALTER is_published SET NOT NULL');
        $this->addSql('ALTER TABLE event_sessions ADD is_published BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE event_sessions ADD uuid UUID NOT NULL');
        $this->addSql('ALTER TABLE event_sessions DROP status');
        $this->addSql('ALTER TABLE event_sessions ALTER start_date TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE event_sessions ALTER start_date DROP NOT NULL');
        $this->addSql('ALTER TABLE event_sessions ALTER end_date TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE event_sessions ALTER end_date DROP NOT NULL');
        $this->addSql('COMMENT ON COLUMN event_sessions.start_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_sessions.end_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DC8C74C3D17F50A6 ON event_sessions (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ALTER is_published DROP DEFAULT');
        $this->addSql('ALTER TABLE event ALTER is_published DROP NOT NULL');
        $this->addSql('DROP INDEX UNIQ_DC8C74C3D17F50A6');
        $this->addSql('ALTER TABLE event_sessions ADD status VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE event_sessions DROP is_published');
        $this->addSql('ALTER TABLE event_sessions DROP uuid');
        $this->addSql('ALTER TABLE event_sessions ALTER start_date TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE event_sessions ALTER start_date SET NOT NULL');
        $this->addSql('ALTER TABLE event_sessions ALTER end_date TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE event_sessions ALTER end_date SET NOT NULL');
        $this->addSql('COMMENT ON COLUMN event_sessions.start_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_sessions.end_date IS \'(DC2Type:datetime_immutable)\'');
    }
}

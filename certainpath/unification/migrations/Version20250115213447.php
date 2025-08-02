<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250115213447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event (id SERIAL NOT NULL, type VARCHAR(255) NOT NULL, json TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN event.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE prospect_event (prospect_id INT NOT NULL, event_id INT NOT NULL, PRIMARY KEY(prospect_id, event_id))');
        $this->addSql('CREATE INDEX IDX_7AD0914DD182060A ON prospect_event (prospect_id)');
        $this->addSql('CREATE INDEX IDX_7AD0914D71F7E88B ON prospect_event (event_id)');
        $this->addSql('ALTER TABLE prospect_event ADD CONSTRAINT FK_7AD0914DD182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE prospect_event ADD CONSTRAINT FK_7AD0914D71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE IF EXISTS prospect_event DROP CONSTRAINT FK_7AD0914DD182060A');
        $this->addSql('ALTER TABLE IF EXISTS prospect_event DROP CONSTRAINT FK_7AD0914D71F7E88B');
        $this->addSql('DROP TABLE IF EXISTS prospect_event');
        $this->addSql('DROP TABLE IF EXISTS event');
    }
}

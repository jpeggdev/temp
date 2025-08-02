<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250425120230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_session_instructor (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE event_sessions DROP CONSTRAINT fk_33d4f045591cc992');
        $this->addSql('ALTER TABLE event_sessions ADD instructor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_sessions ADD CONSTRAINT FK_DC8C74C371F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_sessions ADD CONSTRAINT FK_DC8C74C38C4FC193 FOREIGN KEY (instructor_id) REFERENCES event_session_instructor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_DC8C74C38C4FC193 ON event_sessions (instructor_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_sessions DROP CONSTRAINT FK_DC8C74C38C4FC193');
        $this->addSql('DROP TABLE event_session_instructor');
        $this->addSql('ALTER TABLE event_sessions DROP CONSTRAINT FK_DC8C74C371F7E88B');
        $this->addSql('DROP INDEX IDX_DC8C74C38C4FC193');
        $this->addSql('ALTER TABLE event_sessions DROP instructor_id');
        $this->addSql('ALTER TABLE event_sessions ADD CONSTRAINT fk_33d4f045591cc992 FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250526141029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX unique_event_session_active
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_event_session_active ON event_checkout (created_by_id, event_session_id, company_id) WHERE ((status)::text = 'in_progress'::text)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX unique_event_session_active
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_event_session_active ON event_checkout (created_by_id, event_session_id) WHERE ((status)::text = 'in_progress'::text)
        SQL);
    }
}

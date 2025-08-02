<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250513133036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_instructor ADD email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event_instructor ADD phone VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_69129AF2E7927C74 ON event_instructor (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_69129AF2E7927C74');
        $this->addSql('ALTER TABLE event_instructor DROP email');
        $this->addSql('ALTER TABLE event_instructor DROP phone');
    }
}

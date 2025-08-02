<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250418115717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ADD search_vector TSVECTOR DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD view_count INT DEFAULT 0 NOT NULL');
        $this->addSql('COMMENT ON COLUMN event.search_vector IS \'(DC2Type:tsvector)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP search_vector');
        $this->addSql('ALTER TABLE event DROP view_count');
    }
}

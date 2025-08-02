<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250323145532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource ADD search_vector TSVECTOR DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN resource.search_vector IS \'(DC2Type:tsvector)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource DROP search_vector');
    }
}

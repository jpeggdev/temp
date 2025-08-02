<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218212223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag ADD is_system BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE tag ADD is_active BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE tag ADD is_deleted BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE tag ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN tag.deleted_at IS \'(DC2Type:datetime_immutable)\'');

        $this->addSql("
            UPDATE tag set is_system = true WHERE description = 'Auto-generated version tag'
        ");

        $this->addSql("
        UPDATE tag
        SET name = REGEXP_REPLACE(name, '^v\.', '', 'g')
        WHERE name LIKE 'v.%'
        AND description IS NULL
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE tag DROP is_system');
        $this->addSql('ALTER TABLE tag DROP is_active');
        $this->addSql('ALTER TABLE tag DROP is_deleted');
        $this->addSql('ALTER TABLE tag DROP deleted_at');
    }
}

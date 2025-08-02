<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329130247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql("
        UPDATE resource
        SET slug = lower(regexp_replace(trim(title), '\\s+', '-', 'g'))
    ");
        $this->addSql('ALTER TABLE resource ALTER COLUMN slug SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC91F416989D9B62 ON resource (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource DROP slug');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250529210952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice ADD job_type TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD zone TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD summary TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice DROP job_type');
        $this->addSql('ALTER TABLE invoice DROP zone');
        $this->addSql('ALTER TABLE invoice DROP summary');
    }
}

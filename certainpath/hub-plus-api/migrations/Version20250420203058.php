<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250420203058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_files DROP file_type');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_files ADD file_type VARCHAR(50) NOT NULL');
    }
}

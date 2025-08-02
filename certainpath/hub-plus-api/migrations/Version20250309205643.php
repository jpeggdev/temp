<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250309205643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert Document, Video, and Podcast into resource_type table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO resource_type (name) VALUES
            ('Document'),
            ('Video'),
            ('Podcast')
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DELETE FROM resource_type
            WHERE name IN ('Document', 'Video', 'Podcast')
        ");
    }
}

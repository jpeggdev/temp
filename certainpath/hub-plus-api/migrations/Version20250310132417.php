<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250310132417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sets requires_content_url = true for Video and Podcast in resource_type table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            UPDATE resource_type
            SET requires_content_url = true
            WHERE name IN ('Video', 'Podcast')
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            UPDATE resource_type
            SET requires_content_url = false
            WHERE name IN ('Video', 'Podcast')
        ");
    }
}

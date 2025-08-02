<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250326210234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sets requires_content_url = true where name = "Document"';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE resource_type SET requires_content_url = TRUE WHERE name = 'Document'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE resource_type SET requires_content_url = FALSE WHERE name = 'Document'");
    }
}

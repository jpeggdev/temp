<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250311212845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set is_default = true where name = "Document" in the resource_type table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE resource_type SET is_default = TRUE WHERE name = 'Document'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE resource_type SET is_default = FALSE WHERE name = 'Document'");
    }
}

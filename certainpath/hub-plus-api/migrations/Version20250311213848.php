<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250311213848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a partial unique index on resource_type.is_default (where is_default = TRUE).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE UNIQUE INDEX unique_resource_type_is_default
            ON resource_type (is_default)
            WHERE is_default = TRUE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS unique_resource_type_is_default');
    }
}

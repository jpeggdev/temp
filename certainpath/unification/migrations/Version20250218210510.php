<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250218210510 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Set the default value of updated_at to NULL
        $this->addSql('ALTER TABLE restricted_address ALTER COLUMN updated_at SET DEFAULT now()');
    }

    public function down(Schema $schema): void
    {
        // Revert the default value of updated_at
        $this->addSql('ALTER TABLE restricted_address ALTER COLUMN updated_at DROP DEFAULT');
    }
}

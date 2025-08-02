<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250311140946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove Training Portal from application table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("DELETE FROM application WHERE internal_name = 'training_portal'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO application (name, internal_name)
            SELECT 'Training Portal', 'training_portal'
            WHERE NOT EXISTS (
                SELECT 1 FROM application WHERE internal_name = 'training_portal'
            )
        ");
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250309205734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert some default employee roles: Owner, Manager, HR, etc.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO employee_role (name) VALUES
            ('Owner'),
            ('Manager'),
            ('HR'),
            ('Accountant'),
            ('Supervisor')
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DELETE FROM employee_role
            WHERE name IN ('Owner', 'Manager', 'HR', 'Accountant', 'Supervisor')
        ");
    }
}

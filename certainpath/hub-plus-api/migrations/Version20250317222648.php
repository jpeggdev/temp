<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250317222648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add four default employee session enrollment statuses to employee_session_enrollment_status table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO employee_session_enrollment_status (name, display_name, description, is_active, display_order, created_at, updated_at)
            VALUES
            ('Not Registered', 'Not Registered', 'Employee has not registered for the session.', true, 0, NOW(), NOW()),
            ('Registered', 'Registered', 'Employee has registered for the session.', true, 1, NOW(), NOW()),
            ('Wait Listed', 'Wait Listed', 'Employee is on the wait list for the session.', true, 2, NOW(), NOW()),
            ('Cancelled', 'Cancelled', 'Employee enrollment has been cancelled.', true, 3, NOW(), NOW())
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DELETE FROM employee_session_enrollment_status
            WHERE name IN ('Not Registered', 'Registered', 'Wait Listed', 'Cancelled')
        ");
    }
}

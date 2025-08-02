<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Inserts four employee event statuses into the table `employee_event_status`.
 */
final class Version20250310175414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add four default employee event statuses to employee_event_status table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO employee_event_status (name, display_name, description, is_active, display_order, created_at, updated_at)
            VALUES
            ('enrolled', 'Enrolled', 'Employee has enrolled in the event.', true, 0, NOW(), NOW()),
            ('in_progress', 'In Progress', 'Employee is in progress of completing the event.', true, 1, NOW(), NOW()),
            ('completed', 'Completed', 'Employee has completed the event.', true, 2, NOW(), NOW()),
            ('cancelled', 'Cancelled', 'Employee enrollment has been cancelled.', true, 3, NOW(), NOW())
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DELETE FROM employee_event_status
            WHERE name IN ('enrolled', 'in_progress', 'completed', 'cancelled')
        ");
    }
}

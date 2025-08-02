<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250216160653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert default EmployeeCourseStatus data (enrolled, in_progress, completed, cancelled) into employee_course_status table, skipping rows that already exist.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO employee_course_status (name, display_name, description, is_active, display_order, created_at, updated_at)
            SELECT 'enrolled', 'Enrolled', 'Employee is enrolled in the course but has not started', true, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            WHERE NOT EXISTS (
                SELECT 1 FROM employee_course_status WHERE name = 'enrolled'
            )
        ");

        $this->addSql("
            INSERT INTO employee_course_status (name, display_name, description, is_active, display_order, created_at, updated_at)
            SELECT 'in_progress', 'In Progress', 'Employee has started the course', true, 2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            WHERE NOT EXISTS (
                SELECT 1 FROM employee_course_status WHERE name = 'in_progress'
            )
        ");

        $this->addSql("
            INSERT INTO employee_course_status (name, display_name, description, is_active, display_order, created_at, updated_at)
            SELECT 'completed', 'Completed', 'Employee has completed the course', true, 3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            WHERE NOT EXISTS (
                SELECT 1 FROM employee_course_status WHERE name = 'completed'
            )
        ");

        $this->addSql("
            INSERT INTO employee_course_status (name, display_name, description, is_active, display_order, created_at, updated_at)
            SELECT 'cancelled', 'Cancelled', 'Course enrollment has been cancelled', true, 4, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            WHERE NOT EXISTS (
                SELECT 1 FROM employee_course_status WHERE name = 'cancelled'
            )
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DELETE FROM employee_course_status
            WHERE name IN ('enrolled', 'in_progress', 'completed', 'cancelled')
        ");
    }
}

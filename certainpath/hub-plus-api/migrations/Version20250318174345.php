<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250318174345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Grants "event_registration" application access to all employees with the ROLE_SUPER_ADMIN role.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO application_access (employee_id, application_id, created_at, updated_at)
            SELECT e.id, a.id, NOW(), NOW()
            FROM employee e
            INNER JOIN business_role br ON e.role_id = br.id
            INNER JOIN application a ON a.internal_name = 'event_registration'
            WHERE br.internal_name = 'ROLE_SUPER_ADMIN'
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}

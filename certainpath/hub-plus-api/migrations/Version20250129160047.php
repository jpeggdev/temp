<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250129160047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removes the CAN_CREATE_USERS permission from the permission table';
    }

    public function up(Schema $schema): void
    {
        // No schema changes are needed.
    }

    /**
     * This method runs after the schema has been migrated.
     * Here, we'll remove the permission and its role links.
     */
    public function postUp(Schema $schema): void
    {
        $conn = $this->connection;

        $permissionId = $conn->fetchOne(
            'SELECT id FROM permission WHERE internal_name = :internal_name',
            ['internal_name' => 'CAN_CREATE_USERS']
        );

        if ($permissionId) {
            $conn->executeStatement(
                'DELETE FROM business_role_permission WHERE permission_id = :permission_id',
                ['permission_id' => $permissionId]
            );

            $conn->executeStatement(
                'DELETE FROM permission WHERE id = :permission_id',
                ['permission_id' => $permissionId]
            );
        }
    }

    public function down(Schema $schema): void
    {
    }
}

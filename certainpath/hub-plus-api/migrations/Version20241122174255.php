<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds a new permission to the 'User Management' permission group and assigns it to the 'Super Admin' role.
 */
final class Version20241122174255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds new permission to edit business role permissions and assigns it to Super Admin role';
    }

    public function up(Schema $schema): void
    {
        // No schema changes are needed for this migration.
    }

    public function postUp(Schema $schema): void
    {
        $conn = $this->connection;

        // Define the new permission
        $permissionData = [
            'internal_name' => 'CAN_MANAGE_ROLES_AND_PERMISSIONS',
            'label' => 'Can Manage Business Role Permissions',
            'description' => 'Allows the user to manage the permissions associated with business roles.',
            'isCertainPath' => true,
            'permission_group_name' => 'User Management',
            'roles' => ['ROLE_SUPER_ADMIN'],
        ];

        // Fetch the permission group by name
        $permissionGroupId = $conn->fetchOne(
            'SELECT id FROM permission_group WHERE name = :name',
            ['name' => $permissionData['permission_group_name']]
        );

        if (!$permissionGroupId) {
            throw new \Exception('Permission group not found: '.$permissionData['permission_group_name']);
        }

        // Insert the new permission
        $conn->insert('permission', [
            'internal_name' => $permissionData['internal_name'],
            'label' => $permissionData['label'],
            'description' => $permissionData['description'],
            'permission_group_id' => $permissionGroupId,
            'certain_path' => $permissionData['isCertainPath'] ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $permissionId = $conn->lastInsertId();

        // Assign permission to roles
        foreach ($permissionData['roles'] as $roleInternalName) {
            // Fetch the role by its internal name
            $roleId = $conn->fetchOne(
                'SELECT id FROM business_role WHERE internal_name = :internal_name',
                ['internal_name' => $roleInternalName]
            );

            if ($roleId) {
                // Insert into the business_role_permission table to link the permission and the role
                $conn->insert('business_role_permission', [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                throw new \Exception('Role not found: '.$roleInternalName);
            }
        }
    }

    public function down(Schema $schema): void
    {
        $conn = $this->connection;

        // Fetch the permission by internal_name
        $permissionId = $conn->fetchOne(
            'SELECT id FROM permission WHERE internal_name = :internal_name',
            ['internal_name' => 'CAN_MANAGE_ROLES_AND_PERMISSIONS']
        );

        if ($permissionId) {
            // Delete from business_role_permission
            $conn->executeStatement(
                'DELETE FROM business_role_permission WHERE permission_id = :permission_id',
                ['permission_id' => $permissionId]
            );

            // Delete the permission
            $conn->executeStatement(
                'DELETE FROM permission WHERE id = :permission_id',
                ['permission_id' => $permissionId]
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250201203535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a new CAN_VIEW_STOCHASTIC_MAILING permission under Stochastic Management, assigned to Super Admin and Marketing roles.';
    }

    public function up(Schema $schema): void
    {
        // No schema changes.
    }

    public function postUp(Schema $schema): void
    {
        $conn = $this->connection;

        $permissionData = [
            'internal_name' => 'CAN_VIEW_STOCHASTIC_MAILING',
            'label' => 'Can View Stochastic Mailing',
            'description' => 'Allows the user to view the Stochastic Mailing data for the company.',
            'isCertainPath' => false, // This is usually "false" for Stochastic Management
            'permission_group_name' => 'Stochastic Management',
            'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_MARKETING'],
        ];

        $permissionGroupId = $conn->fetchOne(
            'SELECT id FROM permission_group WHERE name = :name',
            ['name' => $permissionData['permission_group_name']]
        );

        if (!$permissionGroupId) {
            throw new \Exception('Permission group not found: '.$permissionData['permission_group_name']);
        }

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

        foreach ($permissionData['roles'] as $roleInternalName) {
            $roleId = $conn->fetchOne(
                'SELECT id FROM business_role WHERE internal_name = :internal_name',
                ['internal_name' => $roleInternalName]
            );

            if ($roleId) {
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

        // Look up the permission by its internal_name
        $permissionId = $conn->fetchOne(
            'SELECT id FROM permission WHERE internal_name = :internal_name',
            ['internal_name' => 'CAN_VIEW_STOCHASTIC_MAILING']
        );

        if ($permissionId) {
            // Remove any role-permission links
            $conn->executeStatement(
                'DELETE FROM business_role_permission WHERE permission_id = :permission_id',
                ['permission_id' => $permissionId]
            );

            // Remove the permission itself
            $conn->executeStatement(
                'DELETE FROM permission WHERE id = :permission_id',
                ['permission_id' => $permissionId]
            );
        }
    }
}

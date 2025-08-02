<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Inserts campaign management specific permissions and associates them with existing roles.
 */
final class Version20241120052620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Inserts campaign management specific permissions and associates them with existing roles';
    }

    public function up(Schema $schema): void
    {
        // No schema changes required
    }

    public function postUp(Schema $schema): void
    {
        $conn = $this->connection;

        $permissionGroupsData = [
            [
                'name' => 'Campaign Management',
                'description' => 'Manage campaigns and their configuration, including creation, editing, and deletion of campaigns.',
                'isCertainPath' => false,
                'permissions' => [
                    [
                        'internal_name' => 'CAN_MANAGE_CAMPAIGNS',
                        'label' => 'Can Manage Campaigns',
                        'description' => 'Allows the user to manage all aspects of campaigns.',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_MARKETING'],
                        'isCertainPath' => false,
                    ],
                    [
                        'internal_name' => 'CAN_VIEW_CAMPAIGNS',
                        'label' => 'Can View Campaigns',
                        'description' => 'Allows the user to view all campaigns.',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_MARKETING'],
                        'isCertainPath' => false,
                    ],
                    [
                        'internal_name' => 'CAN_CREATE_CAMPAIGNS',
                        'label' => 'Can Create Campaigns',
                        'description' => 'Allows the user to create new campaigns.',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_MARKETING'],
                        'isCertainPath' => false,
                    ],
                    [
                        'internal_name' => 'CAN_EDIT_CAMPAIGNS',
                        'label' => 'Can Edit Campaigns',
                        'description' => 'Allows the user to edit existing campaigns.',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_MARKETING'],
                        'isCertainPath' => false,
                    ],
                    [
                        'internal_name' => 'CAN_MANAGE_CAMPAIGN_BATCHES',
                        'label' => 'Can Manage Campaign Batches',
                        'description' => 'Allows the user to manage campaign batches.',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_MARKETING'],
                        'isCertainPath' => false,
                    ],
                    [
                        'internal_name' => 'CAN_MANAGE_CAMPAIGN_BATCH_PROSPECTS',
                        'label' => 'Can Manage Campaign Batch Prospects',
                        'description' => 'Allows the user to manage prospects within campaign batches.',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_MARKETING'],
                        'isCertainPath' => false,
                    ],
                    [
                        'internal_name' => 'CAN_DELETE_CAMPAIGNS',
                        'label' => 'Can Delete Campaigns',
                        'description' => 'Allows the user to delete campaigns.',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_MARKETING'],
                        'isCertainPath' => false,
                    ],
                    [
                        'internal_name' => 'CAN_EXPORT_CAMPAIGN_DATA',
                        'label' => 'Can Export Campaign Data',
                        'description' => 'Allows the user to export campaign data.',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_MARKETING'],
                        'isCertainPath' => false,
                    ],
                ],
            ],
        ];

        foreach ($permissionGroupsData as $groupData) {
            // Insert the permission group
            $conn->insert('permission_group', [
                'name' => $groupData['name'],
                'description' => $groupData['description'],
                'certain_path' => $groupData['isCertainPath'] ? 1 : 0,
            ]);
            $permissionGroupId = $conn->lastInsertId();

            // Insert each permission and associate with the group and roles
            foreach ($groupData['permissions'] as $permissionData) {
                // Insert permission
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
                    }
                }
            }
        }
    }

    public function down(Schema $schema): void
    {
        // Logic to reverse the migration (deletion of the permissions if necessary)
        $permissionInternalNames = [
            'CAN_VIEW_CAMPAIGN_ALL',
            'CAN_CREATE_CAMPAIGNS',
            'CAN_EDIT_CAMPAIGNS',
            'CAN_DELETE_CAMPAIGNS',
            'CAN_MANAGE_CAMPAIGN_PROSPECTS',
            'CAN_EXPORT_CAMPAIGN_DATA',
            'CAN_MANAGE_CAMPAIGNS',
            'CAN_MANAGE_CAMPAIGN_BATCHES',
            'CAN_MANAGE_CAMPAIGN_BATCH_PROSPECTS',
        ];

        $placeholders = implode(',', array_fill(0, count($permissionInternalNames), '?'));

        // Delete from business_role_permission
        $this->addSql(
            "DELETE FROM business_role_permission WHERE permission_id IN (SELECT id FROM permission WHERE internal_name IN ($placeholders))",
            $permissionInternalNames
        );

        // Delete from permission
        $this->addSql(
            "DELETE FROM permission WHERE internal_name IN ($placeholders)",
            $permissionInternalNames
        );

        // Delete from permission_group
        $this->addSql("DELETE FROM permission_group WHERE name = 'Campaign Management'");
    }
}

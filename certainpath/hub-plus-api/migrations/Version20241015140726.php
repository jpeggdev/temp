<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015140726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Inserts company management specific permissions and associates them with existing roles';
    }

    public function up(Schema $schema): void
    {
    }

    public function postUp(Schema $schema): void
    {
        $conn = $this->connection;

        // Define permission groups and permissions
        $permissionGroupsData = [
            [
                'name' => 'Company Management',
                'description' => 'Manage companies and their configuration, including creation and editing of company details.',
                'isCertainPath' => false,
                'permissions' => [
                    [
                        'internal_name' => 'CAN_MANAGE_COMPANY_ALL',
                        'label' => 'Can Manage Company (All)',
                        'description' => 'Allows the user to manage any company within Certain Path, including accessing all company-related settings and data.',
                        'roles' => ['ROLE_SUPER_ADMIN'],
                        'isCertainPath' => true,
                    ],
                    [
                        'internal_name' => 'CAN_MANAGE_COMPANY_OWN',
                        'label' => 'Can Manage Company (Own)',
                        'description' => 'Allows the user to manage their own company\'s settings and data.',
                        'roles' => ['ROLE_SUPER_ADMIN'],
                        'isCertainPath' => false,
                    ],
                    [
                        'internal_name' => 'CAN_CREATE_COMPANIES',
                        'label' => 'Can Create Companies',
                        'description' => 'Allows the user to create new companies within Certain Path.',
                        'roles' => ['ROLE_SUPER_ADMIN'],
                        'isCertainPath' => true,
                    ],
                    [
                        'internal_name' => 'CAN_EDIT_COMPANIES',
                        'label' => 'Can Edit Companies',
                        'description' => 'Allows the user to edit the details of existing companies within Certain Path.',
                        'roles' => ['ROLE_SUPER_ADMIN'],
                        'isCertainPath' => true,
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
        $this->addSql('DELETE FROM business_role_permission WHERE permission_id IN (SELECT id FROM permission WHERE internal_name IN (\'CAN_MANAGE_COMPANY_ALL\', \'CAN_MANAGE_COMPANY\', \'CAN_CREATE_COMPANIES\', \'CAN_EDIT_COMPANIES\'))');
        $this->addSql('DELETE FROM permission WHERE internal_name IN (\'CAN_MANAGE_COMPANY_ALL\', \'CAN_MANAGE_COMPANY\', \'CAN_CREATE_COMPANIES\', \'CAN_EDIT_COMPANIES\')');
        $this->addSql('DELETE FROM permission_group WHERE name = \'Company Management\'');
    }
}

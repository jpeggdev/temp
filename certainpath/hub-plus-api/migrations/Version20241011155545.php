<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241011155545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert initial roles, permissions, permission groups, and their relationships';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM business_role_permission;');
        $this->addSql('DELETE FROM permission;');
        $this->addSql('DELETE FROM permission_group;');
        $this->addSql('DELETE FROM business_role;');
    }

    public function postUp(Schema $schema): void
    {
        $conn = $this->connection;

        // Define roles
        $rolesData = [
            [
                'internal_name' => 'ROLE_SUPER_ADMIN',
                'label' => 'Super Admin',
                'description' => 'Has full access to all system functions.',
            ],
            [
                'internal_name' => 'ROLE_OWNER_GM',
                'label' => 'Owner/General Manager',
                'description' => 'Manages company settings and high-level functions.',
            ],
            [
                'internal_name' => 'ROLE_MANAGER',
                'label' => 'Manager',
                'description' => 'Oversees team members and daily operations.',
            ],
            [
                'internal_name' => 'ROLE_HR_RECRUITING',
                'label' => 'HR Recruiting',
                'description' => 'Handles recruitment and HR tasks.',
            ],
            [
                'internal_name' => 'ROLE_FINANCE_BACK_OFFICE',
                'label' => 'Finance/Back Office',
                'description' => 'Manages financial records and back-office operations.',
            ],
            [
                'internal_name' => 'ROLE_TECHNICIAN',
                'label' => 'Technician',
                'description' => 'Handles technical tasks and field work.',
            ],
            [
                'internal_name' => 'ROLE_MARKETING',
                'label' => 'Marketing',
                'description' => 'Oversees marketing campaigns and strategies.',
            ],
            [
                'internal_name' => 'ROLE_CALL_CENTER',
                'label' => 'Call Center',
                'description' => 'Manages customer calls and support.',
            ],
            [
                'internal_name' => 'ROLE_SALES',
                'label' => 'Sales',
                'description' => 'Handles sales operations and client relationships.',
            ],
        ];

        $roleIds = [];
        foreach ($rolesData as $roleData) {
            $conn->executeStatement(
                'INSERT INTO business_role (id, internal_name, label, description, created_at, updated_at) VALUES (DEFAULT, :internal_name, :label, :description, :created_at, :updated_at)',
                [
                    'internal_name' => $roleData['internal_name'],
                    'label' => $roleData['label'],
                    'description' => $roleData['description'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]
            );

            // Fetch the last inserted ID for the business_role
            $roleIds[$roleData['internal_name']] = $conn->lastInsertId();
        }

        // Define permission groups and permissions
        $permissionGroupsData = [
            // Universal Access Group
            [
                'name' => 'Universal Access',
                'description' => 'Site-wide permissions that allow users to switch between companies and manage system-wide operations.',
                'isCertainPath' => true,
                'permissions' => [
                    [
                        'internal_name' => 'CAN_SWITCH_COMPANY_ALL',
                        'label' => 'Can Switch Company (All)',
                        'description' => 'Allows the user to switch their session to any company within Certain Path, temporarily assuming the same role they have in Certain Path across the selected company.',
                        'roles' => ['ROLE_SUPER_ADMIN'],
                        'isCertainPath' => true,
                    ],
                    [
                        'internal_name' => 'CAN_SWITCH_COMPANY_MARKETING_ONLY',
                        'label' => 'Can Switch Company (Marketing Only)',
                        'description' => 'Allows the user to switch their session to a marketing-enabled company within Certain Path, temporarily assuming the same role they have in Certain Path across the selected company.',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_MARKETING'],
                        'isCertainPath' => true,
                    ],
                ],
            ],
            // User Management Group
            [
                'name' => 'User Management',
                'description' => 'Manage users and their roles.',
                'isCertainPath' => false,
                'permissions' => [
                    [
                        'internal_name' => 'CAN_MANAGE_USERS',
                        'label' => 'Can Manage Users',
                        'description' => 'Able to create, update, delete users and manage their permissions and roles for the company.',
                        'roles' => ['ROLE_SUPER_ADMIN'],
                    ],
                    [
                        'internal_name' => 'CAN_VIEW_USERS',
                        'label' => 'Can View Users',
                        'description' => 'Able to view user information, roles, and permissions for the company.',
                        'roles' => ['ROLE_SUPER_ADMIN'],
                    ],
                    [
                        'internal_name' => 'CAN_CREATE_USERS',
                        'label' => 'Can Create Users',
                        'description' => 'Able to create new users for the company.',
                        'roles' => ['ROLE_SUPER_ADMIN'],
                    ],
                ],
            ],
            // Document Management Group
            [
                'name' => 'Document Management',
                'description' => 'Access and manage company documents.',
                'isCertainPath' => false,
                'permissions' => [
                    [
                        'internal_name' => 'CAN_ACCESS_DOCUMENT_LIBRARY',
                        'label' => 'Can Access Document Library',
                        'description' => 'Able to download and view reports such as monthly balance sheet, profit and loss, and transactions for company.',
                        'roles' => ['ROLE_SUPER_ADMIN'],
                    ],
                    [
                        'internal_name' => 'CAN_ACCESS_MONTHLY_BALANCE_SHEET',
                        'label' => 'Can Access Monthly Balance Sheet',
                        'description' => 'Able to download and view the monthly balance sheet for the company.',
                        'roles' => ['ROLE_SUPER_ADMIN'],
                    ],
                    [
                        'internal_name' => 'CAN_ACCESS_PROFIT_AND_LOSS',
                        'label' => 'Can Access Profit and Loss',
                        'description' => 'Able to download and view the profit and loss statement for the company.',
                        'roles' => ['ROLE_SUPER_ADMIN'],
                    ],
                    [
                        'internal_name' => 'CAN_ACCESS_TRANSACTION_LIST',
                        'label' => 'Can Access Transaction List',
                        'description' => 'Able to download and view the transaction list for the company.',
                        'roles' => ['ROLE_SUPER_ADMIN'],
                    ],
                ],
            ],
            // Data Access Group
            [
                'name' => 'Data Access',
                'description' => 'Access company data and connectors.',
                'isCertainPath' => false,
                'permissions' => [
                    [
                        'internal_name' => 'CAN_ACCESS_DATA_CONNECTOR',
                        'label' => 'Can Access Data Connector',
                        'description' => 'Able to access Hot Glue data connector for company.',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_MARKETING'],
                    ],
                ],
            ],
            // Stochastic Management Group
            [
                'name' => 'Stochastic Management',
                'description' => 'Manage and view stochastic prospects and customers for the company.',
                'isCertainPath' => false,
                'permissions' => [
                    [
                        'internal_name' => 'CAN_MANAGE_PROSPECTS',
                        'label' => 'Can Manage Prospects',
                        'description' => 'Able to manage and view stochastic prospects for company.',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_MARKETING'],
                    ],
                    [
                        'internal_name' => 'CAN_MANAGE_CUSTOMERS',
                        'label' => 'Can Manage Customers',
                        'description' => 'Able to manage and view stochastic customers for company.',
                        'roles' => ['ROLE_SUPER_ADMIN', 'ROLE_MARKETING'],
                    ],
                ],
            ],
        ];

        $permissionIds = [];
        foreach ($permissionGroupsData as $groupData) {
            $conn->insert('permission_group', [
                'name' => $groupData['name'],
                'description' => $groupData['description'],
                'certain_path' => $groupData['isCertainPath'] ? 1 : 0,
            ]);
            $permissionGroupId = $conn->lastInsertId();

            foreach ($groupData['permissions'] as $permissionData) {
                $conn->insert('permission', [
                    'internal_name' => $permissionData['internal_name'],
                    'label' => $permissionData['label'],
                    'description' => $permissionData['description'],
                    'permission_group_id' => $permissionGroupId,
                    'certain_path' => isset($permissionData['isCertainPath']) && $permissionData['isCertainPath'] ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $permissionId = $conn->lastInsertId();
                $permissionIds[$permissionData['internal_name']] = $permissionId;

                // Assign permissions to roles
                foreach ($permissionData['roles'] as $roleInternalName) {
                    if (!isset($roleIds[$roleInternalName])) {
                        throw new \Exception("Role '{$roleInternalName}' is not defined.");
                    }

                    $conn->insert('business_role_permission', [
                        'role_id' => $roleIds[$roleInternalName],
                        'permission_id' => $permissionId,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM business_role_permission;');
        $this->addSql('DELETE FROM permission;');
        $this->addSql('DELETE FROM permission_group;');
        $this->addSql('DELETE FROM business_role;');
    }
}

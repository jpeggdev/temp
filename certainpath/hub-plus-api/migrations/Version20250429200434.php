<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\BusinessRole;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429200434 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO business_role (internal_name, label, description, sort_order, created_at, updated_at, certain_path) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                BusinessRole::ROLE_COACH,
                BusinessRole::ROLE_COACH_LABEL,
                BusinessRole::ROLE_COACH_DESCRIPTION,
                0,
                'now()',
                'now()',
                true,
            ]
        );
        $this->addSql(
            "
INSERT INTO business_role_permission (role_id, permission_id, created_at, updated_at)
SELECT
    (SELECT id FROM business_role WHERE internal_name = 'ROLE_COACH'),
    p.id,
    NOW(),
    NOW()
FROM permission p
WHERE p.internal_name IN (
    'CAN_SWITCH_COMPANY_ALL',
    'CAN_VIEW_USERS',
    'CAN_MANAGE_USERS',
    'CAN_ACCESS_DOCUMENT_LIBRARY',
    'CAN_ACCESS_MONTHLY_BALANCE_SHEET',
    'CAN_ACCESS_PROFIT_AND_LOSS',
    'CAN_ACCESS_TRANSACTION_LIST',
    'CAN_ACCESS_DATA_CONNECTOR',
    'CAN_MANAGE_PROSPECTS',
    'CAN_MANAGE_CUSTOMERS',
    'CAN_VIEW_STOCHASTIC_MAILING',
    'CAN_MANAGE_CAMPAIGNS',
    'CAN_VIEW_CAMPAIGNS',
    'CAN_CREATE_CAMPAIGNS',
    'CAN_EDIT_CAMPAIGNS',
    'CAN_MANAGE_CAMPAIGN_BATCHES',
    'CAN_MANAGE_CAMPAIGN_BATCH_PROSPECTS',
    'CAN_DELETE_CAMPAIGNS',
    'CAN_EXPORT_CAMPAIGN_DATA'
);
            "
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'DELETE FROM business_role_permission WHERE role_id = (SELECT id FROM business_role WHERE internal_name = ?)',
            [BusinessRole::ROLE_COACH]
        );
        $this->addSql(
            'DELETE FROM business_role WHERE internal_name = ?',
            [BusinessRole::ROLE_COACH]
        );
    }
}

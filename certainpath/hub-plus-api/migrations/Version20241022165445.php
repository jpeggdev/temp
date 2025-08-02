<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241022165445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixes the internal_name and label for the CAN_MANAGE_COMPANY_ALL permission to CAN_MANAGE_COMPANIES_ALL';
    }

    public function up(Schema $schema): void
    {
        // Update the internal_name and label in the permission table
        $this->addSql(
            "UPDATE permission SET internal_name = 'CAN_MANAGE_COMPANIES_ALL', label = 'Can Manage Companies (All)' WHERE internal_name = 'CAN_MANAGE_COMPANY_ALL'"
        );
    }

    public function down(Schema $schema): void
    {
        // Reverse the change, in case the migration is rolled back
        $this->addSql(
            "UPDATE permission SET internal_name = 'CAN_MANAGE_COMPANY_ALL', label = 'Can Manage Company (All)' WHERE internal_name = 'CAN_MANAGE_COMPANIES_ALL'"
        );
    }
}

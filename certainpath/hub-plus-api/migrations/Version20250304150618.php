<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250304150618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds certain_path column to business_role and updates it to true for ROLE_SUPER_ADMIN & ROLE_MARKETING.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE business_role ADD certain_path BOOLEAN DEFAULT false NOT NULL');
        $this->addSql("UPDATE business_role SET certain_path = true WHERE internal_name IN ('ROLE_SUPER_ADMIN', 'ROLE_MARKETING')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE business_role DROP certain_path');
    }
}

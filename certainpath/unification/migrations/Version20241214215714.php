<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241214215714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the archived status to the campaign_status table.';
    }

    public function up(Schema $schema): void
    {
        // Insert the new "archived" campaign status
        $this->addSql("INSERT INTO campaign_status (name, created_at, updated_at) VALUES ('archived', NOW(), NOW())");
    }

    public function down(Schema $schema): void
    {
        // Remove the "archived" campaign status
        $this->addSql("DELETE FROM campaign_status WHERE name = 'archived'");
    }
}

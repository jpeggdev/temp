<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250522101714 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            DELETE FROM email_campaign_status
            WHERE name IN ('failed', 'sending')
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO email_campaign_status (name) VALUES ('failed'), ('sending')
        ");
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250124184042 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE IF EXISTS campaign
            ADD mailing_drop_weeks JSON NOT NULL DEFAULT \'[]\'
        ');
        $this->addSql('
            ALTER TABLE IF EXISTS campaign_iteration_week
            ADD is_mailing_drop_week BOOLEAN NOT NULL DEFAULT FALSE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE IF EXISTS campaign
            DROP COLUMN IF EXISTS mailing_drop_weeks
        ');
        $this->addSql('
            ALTER TABLE IF EXISTS campaign_iteration_week
            DROP COLUMN IF EXISTS is_mailing_drop_week
        ');
    }
}

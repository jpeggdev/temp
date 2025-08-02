<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250520204949 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE email_campaign
            ADD is_active BOOLEAN DEFAULT true NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE email_campaign
            ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN email_campaign.deleted_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE email_campaign DROP IF EXISTS recipient_count
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE email_campaign DROP is_active
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE email_campaign DROP deleted_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE email_campaign ADD recipient_count INT NOT NULL DEFAULT 0
        SQL);
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250422023708 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE email_campaign_activity_log (
                id SERIAL NOT NULL,
                message_id TEXT NOT NULL,
                email TEXT NOT NULL,
                subject TEXT DEFAULT NULL,
                is_sent BOOLEAN DEFAULT false NOT NULL,
                is_delivered BOOLEAN DEFAULT false NOT NULL,
                is_opened BOOLEAN DEFAULT false NOT NULL,
                is_clicked BOOLEAN DEFAULT false NOT NULL,
                is_bounced BOOLEAN DEFAULT false NOT NULL,
                is_marked_as_spam BOOLEAN DEFAULT false NOT NULL,
                event_sent_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id)
            )
        ');

        $this->addSql('
            COMMENT ON COLUMN email_campaign_activity_log.event_sent_at IS \'(DC2Type:datetimetz_immutable)\'
        ');
        $this->addSql('
            COMMENT ON COLUMN email_campaign_activity_log.created_at IS \'(DC2Type:datetimetz_immutable)\'
        ');
        $this->addSql('
            COMMENT ON COLUMN email_campaign_activity_log.updated_at IS \'(DC2Type:datetimetz_immutable)\'
        ');

        $this->addSql('
            CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_A4A3693B537A1329
            ON email_campaign_activity_log (message_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS UNIQ_A4A3693B537A1329');
        $this->addSql('DROP TABLE email_campaign_activity_log');
    }
}

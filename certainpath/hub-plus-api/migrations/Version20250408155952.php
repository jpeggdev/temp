<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\EmailCampaignStatus;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250408155952 extends AbstractMigration
{
    private const array EmailCampaignStatuses = [
        EmailCampaignStatus::STATUS_ARCHIVED,
        EmailCampaignStatus::STATUS_DRAFT,
        EmailCampaignStatus::STATUS_SENDING,
        EmailCampaignStatus::STATUS_SENT,
        EmailCampaignStatus::STATUS_SCHEDULED,
        EmailCampaignStatus::STATUS_FAILED,
    ];

    public function up(Schema $schema): void
    {
        $this->createEmailCampaignStatusTable();
        $this->createEmailCampaignTable();
        $this->createEmailCampaignEmployeeSessionEnrollmentTable();
        $this->insertEmailCampaignStatuses();
    }

    public function down(Schema $schema): void
    {
        $this->dropEmailCampaignEmployeeSessionEnrollmentTable();
        $this->dropEmailCampaignTable();
        $this->dropEmailCampaignStatusTable();
    }

    private function createEmailCampaignStatusTable(): void
    {
        $this->addSql('
            CREATE TABLE email_campaign_status (
                id SERIAL NOT NULL,
                name TEXT NOT NULL,
                PRIMARY KEY(id)
            )
        ');
    }

    private function createEmailCampaignTable(): void
    {
        $this->addSql('
            CREATE TABLE email_campaign (
                id SERIAL NOT NULL,
                email_template_id INT NOT NULL,
                event_id INT NOT NULL,
                event_session_id INT NOT NULL,
                email_campaign_status_id INT NOT NULL,
                campaign_name TEXT NOT NULL,
                description TEXT DEFAULT NULL,
                email_subject TEXT DEFAULT NULL,
                recipient_count INT NOT NULL,
                date_sent TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('COMMENT ON COLUMN email_campaign.date_sent IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN email_campaign.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN email_campaign.updated_at IS \'(DC2Type:datetimetz_immutable)\'');

        $this->addSql('
            CREATE INDEX IDX_14730D94131A730F
            ON email_campaign (email_template_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_14730D9471F7E88B
            ON email_campaign (event_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_14730D9439D135F0
            ON email_campaign (event_session_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_14730D94AE2A40CA
            ON email_campaign (email_campaign_status_id)
        ');

        $this->addSql('
            ALTER TABLE email_campaign
            ADD CONSTRAINT FK_14730D94131A730F
            FOREIGN KEY (email_template_id)
            REFERENCES email_template (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE email_campaign
            ADD CONSTRAINT FK_14730D9471F7E88B
            FOREIGN KEY (event_id)
            REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE email_campaign
            ADD CONSTRAINT FK_14730D9439D135F0
            FOREIGN KEY (event_session_id)
            REFERENCES event_sessions (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('
            ALTER TABLE email_campaign
            ADD CONSTRAINT FK_14730D94AE2A40CA
            FOREIGN KEY (email_campaign_status_id)
            REFERENCES email_campaign_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    private function createEmailCampaignEmployeeSessionEnrollmentTable(): void
    {
        $this->addSql('
            CREATE TABLE email_campaign_employee_session_enrollment (
                id SERIAL NOT NULL,
                email_campaign_id INT NOT NULL,
                employee_session_enrollment_id INT NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('
            CREATE INDEX IDX_283002E6E0F98BC3
            ON email_campaign_employee_session_enrollment (email_campaign_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_283002E6A87F0FE4
            ON email_campaign_employee_session_enrollment (employee_session_enrollment_id)
        ');

        $this->addSql('
            ALTER TABLE email_campaign_employee_session_enrollment
            ADD CONSTRAINT FK_283002E6E0F98BC3
            FOREIGN KEY (email_campaign_id)
            REFERENCES email_campaign (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');

        $this->addSql('
            ALTER TABLE email_campaign_employee_session_enrollment
            ADD CONSTRAINT FK_283002E6A87F0FE4 FOREIGN KEY (employee_session_enrollment_id)
            REFERENCES employee_session_enrollment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    private function insertEmailCampaignStatuses(): void
    {
        foreach (self::EmailCampaignStatuses as $emailCampaignStatus) {
            $this->addSql(
                'INSERT INTO email_campaign_status (name) VALUES (:name)',
                ['name' => $emailCampaignStatus]
            );
        }
    }

    private function dropEmailCampaignEmployeeSessionEnrollmentTable(): void
    {
        $this->addSql('
            ALTER TABLE email_campaign_employee_session_enrollment
            DROP CONSTRAINT FK_283002E6E0F98BC3
        ');
        $this->addSql('
            ALTER TABLE email_campaign_employee_session_enrollment
            DROP CONSTRAINT FK_283002E6A87F0FE4
        ');

        $this->addSql('DROP INDEX IDX_283002E6E0F98BC3');
        $this->addSql('DROP INDEX IDX_283002E6A87F0FE4');

        $this->addSql('DROP TABLE email_campaign_employee_session_enrollment');
    }

    private function dropEmailCampaignTable(): void
    {
        $this->addSql('ALTER TABLE email_campaign DROP CONSTRAINT FK_14730D94131A730F');
        $this->addSql('ALTER TABLE email_campaign DROP CONSTRAINT FK_14730D9471F7E88B');
        $this->addSql('ALTER TABLE email_campaign DROP CONSTRAINT FK_14730D9439D135F0');
        $this->addSql('ALTER TABLE email_campaign DROP CONSTRAINT FK_14730D94AE2A40CA');
        $this->addSql('DROP TABLE email_campaign');
    }

    private function dropEmailCampaignStatusTable(): void
    {
        $this->addSql('DROP TABLE email_campaign_status');
    }
}

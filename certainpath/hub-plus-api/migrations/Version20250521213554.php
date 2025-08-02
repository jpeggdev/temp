<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250521213554 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->dropEmailCampaignEmployeeSessionEnrollmentTable();
        $this->createEmailCampaignEventEnrollmentTable();
    }

    public function down(Schema $schema): void
    {
        $this->dropEmailCampaignEventEnrollmentTable();
        $this->restoreEmailCampaignEmployeeSessionEnrollmentTable();
    }

    private function dropEmailCampaignEmployeeSessionEnrollmentTable(): void
    {
        $this->addSql('DROP SEQUENCE IF EXISTS email_campaign_employee_session_enrollment_id_seq CASCADE');
        $this->addSql('
            ALTER TABLE email_campaign_employee_session_enrollment
            DROP CONSTRAINT IF EXISTS fk_283002e6e0f98bc3
        ');
        $this->addSql('DROP TABLE email_campaign_employee_session_enrollment');
    }

    private function createEmailCampaignEventEnrollmentTable(): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE email_campaign_event_enrollment (
                id SERIAL NOT NULL,
                email_campaign_id INT NOT NULL,
                event_enrollment_id INT NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql('
            CREATE INDEX IDX_95534FDBE0F98BC3
            ON email_campaign_event_enrollment (email_campaign_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_95534FDBAB2402F7
            ON email_campaign_event_enrollment (event_enrollment_id)
        ');

        $this->addSql(<<<'SQL'
            ALTER TABLE email_campaign_event_enrollment
            ADD CONSTRAINT FK_95534FDBE0F98BC3
            FOREIGN KEY (email_campaign_id) REFERENCES email_campaign (id)
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE email_campaign_event_enrollment
            ADD CONSTRAINT FK_95534FDBAB2402F7
            FOREIGN KEY (event_enrollment_id) REFERENCES event_enrollment (id)
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    private function dropEmailCampaignEventEnrollmentTable(): void
    {
        $this->addSql('
            ALTER TABLE email_campaign_event_enrollment
            DROP CONSTRAINT IF EXISTS FK_95534FDBE0F98BC3
        ');
        $this->addSql('
            ALTER TABLE email_campaign_event_enrollment
            DROP CONSTRAINT IF EXISTS FK_95534FDBAB2402F7
        ');
        $this->addSql('DROP TABLE email_campaign_event_enrollment');
    }

    private function restoreEmailCampaignEmployeeSessionEnrollmentTable(): void
    {
        $this->addSql('
            CREATE SEQUENCE email_campaign_employee_session_enrollment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

        $this->addSql(<<<'SQL'
            CREATE TABLE email_campaign_employee_session_enrollment (
                id SERIAL NOT NULL,
                email_campaign_id INT NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql('
            CREATE INDEX idx_283002e6e0f98bc3
            ON email_campaign_employee_session_enrollment (email_campaign_id)
        ');

        $this->addSql(<<<'SQL'
            ALTER TABLE email_campaign_employee_session_enrollment
            ADD CONSTRAINT fk_283002e6e0f98bc3
            FOREIGN KEY (email_campaign_id) REFERENCES email_campaign (id)
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }
}

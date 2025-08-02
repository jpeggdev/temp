<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250531173951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE event_enrollment_waitlist (id SERIAL NOT NULL, event_session_id INT NOT NULL, employee_id INT DEFAULT NULL, original_checkout_id INT DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, special_requests TEXT DEFAULT NULL, waitlisted_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, waitlist_position INT DEFAULT NULL, promoted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_44C462AD39D135F0 ON event_enrollment_waitlist (event_session_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_44C462AD8C03F15C ON event_enrollment_waitlist (employee_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_44C462ADE362670E ON event_enrollment_waitlist (original_checkout_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN event_enrollment_waitlist.waitlisted_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN event_enrollment_waitlist.promoted_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN event_enrollment_waitlist.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN event_enrollment_waitlist.updated_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_enrollment_waitlist ADD CONSTRAINT FK_44C462AD39D135F0 FOREIGN KEY (event_session_id) REFERENCES event_session (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_enrollment_waitlist ADD CONSTRAINT FK_44C462AD8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_enrollment_waitlist ADD CONSTRAINT FK_44C462ADE362670E FOREIGN KEY (original_checkout_id) REFERENCES event_checkout (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE event_enrollment_waitlist DROP CONSTRAINT FK_44C462AD39D135F0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_enrollment_waitlist DROP CONSTRAINT FK_44C462AD8C03F15C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_enrollment_waitlist DROP CONSTRAINT FK_44C462ADE362670E
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event_enrollment_waitlist
        SQL);
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507003531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email_campaign DROP CONSTRAINT IF EXISTS fk_14730d9439d135f0');
        $this->addSql('ALTER TABLE email_campaign_employee_session_enrollment DROP CONSTRAINT IF EXISTS fk_283002e6a87f0fe4');
        $this->addSql('DROP SEQUENCE IF EXISTS event_sessions_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS employee_session_enrollment_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS employee_session_enrollment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS event_session_instructor_id_seq CASCADE');
        $this->addSql('CREATE TABLE event_checkout (id SERIAL NOT NULL, created_by_id INT NOT NULL, event_session_id INT NOT NULL, contact_name VARCHAR(255) NOT NULL, contact_email VARCHAR(255) NOT NULL, contact_phone VARCHAR(255) DEFAULT NULL, group_notes TEXT DEFAULT NULL, reservation_expires_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, completed_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, status VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E9D4C49EB03A8386 ON event_checkout (created_by_id)');
        $this->addSql('CREATE INDEX IDX_E9D4C49E39D135F0 ON event_checkout (event_session_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E9D4C49ED17F50A6 ON event_checkout (uuid)');
        $this->addSql('CREATE UNIQUE INDEX unique_event_session_active ON event_checkout (created_by_id, event_session_id) WHERE ((status)::text = \'in_progress\'::text)');
        $this->addSql('COMMENT ON COLUMN event_checkout.reservation_expires_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_checkout.completed_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_checkout.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_checkout.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE event_checkout_attendee (id SERIAL NOT NULL, event_checkout_id INT NOT NULL, employee_id INT DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, special_requests TEXT DEFAULT NULL, is_selected BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ADEC2D7ABFE454A4 ON event_checkout_attendee (event_checkout_id)');
        $this->addSql('CREATE INDEX IDX_ADEC2D7A8C03F15C ON event_checkout_attendee (employee_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ADEC2D7A8C03F15CBFE454A4 ON event_checkout_attendee (employee_id, event_checkout_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ADEC2D7AE7927C74BFE454A4 ON event_checkout_attendee (email, event_checkout_id)');
        $this->addSql('COMMENT ON COLUMN event_checkout_attendee.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_checkout_attendee.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE event_discount_ledger (id SERIAL NOT NULL, event_checkout_id INT NOT NULL, type VARCHAR(255) NOT NULL, code VARCHAR(255) DEFAULT NULL, amount NUMERIC(10, 2) NOT NULL, reason VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_67E8ECB3BFE454A4 ON event_discount_ledger (event_checkout_id)');
        $this->addSql('CREATE TABLE event_enrollment (id SERIAL NOT NULL, event_checkout_id INT NOT NULL, employee_id INT NOT NULL, event_session_id INT NOT NULL, enrolled_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_83320ECEBFE454A4 ON event_enrollment (event_checkout_id)');
        $this->addSql('CREATE INDEX IDX_83320ECE8C03F15C ON event_enrollment (employee_id)');
        $this->addSql('CREATE INDEX IDX_83320ECE39D135F0 ON event_enrollment (event_session_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_83320ECE8C03F15C39D135F0 ON event_enrollment (employee_id, event_session_id)');
        $this->addSql('COMMENT ON COLUMN event_enrollment.enrolled_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_enrollment.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_enrollment.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE event_instructor (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE event_session (id SERIAL NOT NULL, event_id INT NOT NULL, instructor_id INT DEFAULT NULL, start_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, end_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, max_enrollments INT NOT NULL, virtual_link VARCHAR(255) DEFAULT NULL, notes TEXT DEFAULT NULL, is_published BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_55137C7B71F7E88B ON event_session (event_id)');
        $this->addSql('CREATE INDEX IDX_55137C7B8C4FC193 ON event_session (instructor_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_55137C7BD17F50A6 ON event_session (uuid)');
        $this->addSql('COMMENT ON COLUMN event_session.start_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_session.end_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_session.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_session.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE event_voucher_ledger (id SERIAL NOT NULL, seats_used INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE event_checkout ADD CONSTRAINT FK_E9D4C49EB03A8386 FOREIGN KEY (created_by_id) REFERENCES employee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_checkout ADD CONSTRAINT FK_E9D4C49E39D135F0 FOREIGN KEY (event_session_id) REFERENCES event_session (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_checkout_attendee ADD CONSTRAINT FK_ADEC2D7ABFE454A4 FOREIGN KEY (event_checkout_id) REFERENCES event_checkout (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_checkout_attendee ADD CONSTRAINT FK_ADEC2D7A8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_discount_ledger ADD CONSTRAINT FK_67E8ECB3BFE454A4 FOREIGN KEY (event_checkout_id) REFERENCES event_checkout (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_enrollment ADD CONSTRAINT FK_83320ECEBFE454A4 FOREIGN KEY (event_checkout_id) REFERENCES event_checkout (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_enrollment ADD CONSTRAINT FK_83320ECE8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_enrollment ADD CONSTRAINT FK_83320ECE39D135F0 FOREIGN KEY (event_session_id) REFERENCES event_session (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_session ADD CONSTRAINT FK_55137C7B71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_session ADD CONSTRAINT FK_55137C7B8C4FC193 FOREIGN KEY (instructor_id) REFERENCES event_instructor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_sessions DROP CONSTRAINT IF EXISTS fk_dc8c74c371f7e88b');
        $this->addSql('ALTER TABLE event_sessions DROP CONSTRAINT IF EXISTS fk_dc8c74c38c4fc193');
        $this->addSql('ALTER TABLE employee_session_enrollment DROP CONSTRAINT IF EXISTS fk_7725abba8c03f15c');
        $this->addSql('ALTER TABLE employee_session_enrollment DROP CONSTRAINT IF EXISTS fk_7725abba39d135f0');
        $this->addSql('ALTER TABLE employee_session_enrollment DROP CONSTRAINT IF EXISTS fk_7725abba6bf700bd');
        $this->addSql('DROP TABLE IF EXISTS event_session_instructor');
        $this->addSql('DROP TABLE IF EXISTS event_sessions');
        $this->addSql('DROP TABLE IF EXISTS employee_session_enrollment_status');
        $this->addSql('DROP TABLE IF EXISTS employee_session_enrollment');
        $this->addSql('ALTER TABLE email_campaign DROP CONSTRAINT IF EXISTS FK_14730D9439D135F0');
        $this->addSql('ALTER TABLE email_campaign ADD CONSTRAINT FK_14730D9439D135F0 FOREIGN KEY (event_session_id) REFERENCES event_session (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX IF EXISTS idx_283002e6a87f0fe4');
        $this->addSql('ALTER TABLE email_campaign_employee_session_enrollment DROP employee_session_enrollment_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email_campaign DROP CONSTRAINT FK_14730D9439D135F0');
        $this->addSql('CREATE SEQUENCE event_sessions_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE employee_session_enrollment_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE employee_session_enrollment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE event_session_instructor_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE event_session_instructor (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE event_sessions (id SERIAL NOT NULL, event_id INT NOT NULL, instructor_id INT DEFAULT NULL, start_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, end_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, max_enrollments INT NOT NULL, virtual_link VARCHAR(255) DEFAULT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, is_published BOOLEAN DEFAULT false NOT NULL, uuid UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_dc8c74c38c4fc193 ON event_sessions (instructor_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_dc8c74c3d17f50a6 ON event_sessions (uuid)');
        $this->addSql('CREATE INDEX idx_dc8c74c371f7e88b ON event_sessions (event_id)');
        $this->addSql('COMMENT ON COLUMN event_sessions.start_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_sessions.end_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_sessions.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_sessions.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE employee_session_enrollment_status (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, display_name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, display_order INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_8a244b0d5e237e06 ON employee_session_enrollment_status (name)');
        $this->addSql('COMMENT ON COLUMN employee_session_enrollment_status.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_session_enrollment_status.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE employee_session_enrollment (id SERIAL NOT NULL, employee_id INT NOT NULL, event_session_id INT NOT NULL, status_id INT NOT NULL, enrollment_date TIMESTAMP(0) WITH TIME ZONE NOT NULL, completion_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, progress INT NOT NULL, completed BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX unique_employee_session_enrollment ON employee_session_enrollment (employee_id, event_session_id)');
        $this->addSql('CREATE INDEX idx_7725abba6bf700bd ON employee_session_enrollment (status_id)');
        $this->addSql('CREATE INDEX idx_7725abba39d135f0 ON employee_session_enrollment (event_session_id)');
        $this->addSql('CREATE INDEX idx_7725abba8c03f15c ON employee_session_enrollment (employee_id)');
        $this->addSql('COMMENT ON COLUMN employee_session_enrollment.enrollment_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_session_enrollment.completion_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_session_enrollment.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_session_enrollment.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE event_sessions ADD CONSTRAINT fk_dc8c74c371f7e88b FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_sessions ADD CONSTRAINT fk_dc8c74c38c4fc193 FOREIGN KEY (instructor_id) REFERENCES event_session_instructor (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_session_enrollment ADD CONSTRAINT fk_7725abba8c03f15c FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_session_enrollment ADD CONSTRAINT fk_7725abba39d135f0 FOREIGN KEY (event_session_id) REFERENCES event_sessions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_session_enrollment ADD CONSTRAINT fk_7725abba6bf700bd FOREIGN KEY (status_id) REFERENCES employee_session_enrollment_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_checkout DROP CONSTRAINT FK_E9D4C49EB03A8386');
        $this->addSql('ALTER TABLE event_checkout DROP CONSTRAINT FK_E9D4C49E39D135F0');
        $this->addSql('ALTER TABLE event_checkout_attendee DROP CONSTRAINT FK_ADEC2D7ABFE454A4');
        $this->addSql('ALTER TABLE event_checkout_attendee DROP CONSTRAINT FK_ADEC2D7A8C03F15C');
        $this->addSql('ALTER TABLE event_discount_ledger DROP CONSTRAINT FK_67E8ECB3BFE454A4');
        $this->addSql('ALTER TABLE event_enrollment DROP CONSTRAINT FK_83320ECEBFE454A4');
        $this->addSql('ALTER TABLE event_enrollment DROP CONSTRAINT FK_83320ECE8C03F15C');
        $this->addSql('ALTER TABLE event_enrollment DROP CONSTRAINT FK_83320ECE39D135F0');
        $this->addSql('ALTER TABLE event_session DROP CONSTRAINT FK_55137C7B71F7E88B');
        $this->addSql('ALTER TABLE event_session DROP CONSTRAINT FK_55137C7B8C4FC193');
        $this->addSql('DROP TABLE event_checkout');
        $this->addSql('DROP TABLE event_checkout_attendee');
        $this->addSql('DROP TABLE event_discount_ledger');
        $this->addSql('DROP TABLE event_enrollment');
        $this->addSql('DROP TABLE event_instructor');
        $this->addSql('DROP TABLE event_session');
        $this->addSql('DROP TABLE event_voucher_ledger');
        $this->addSql('ALTER TABLE email_campaign_employee_session_enrollment ADD employee_session_enrollment_id INT NOT NULL');
        $this->addSql('ALTER TABLE email_campaign_employee_session_enrollment ADD CONSTRAINT fk_283002e6a87f0fe4 FOREIGN KEY (employee_session_enrollment_id) REFERENCES employee_session_enrollment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_283002e6a87f0fe4 ON email_campaign_employee_session_enrollment (employee_session_enrollment_id)');
        $this->addSql('ALTER TABLE email_campaign DROP CONSTRAINT fk_14730d9439d135f0');
        $this->addSql('ALTER TABLE email_campaign ADD CONSTRAINT fk_14730d9439d135f0 FOREIGN KEY (event_session_id) REFERENCES event_sessions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

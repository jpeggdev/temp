<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250317194213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE employee_event_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE employee_event_status_id_seq CASCADE');
        $this->addSql('CREATE TABLE employee_session_enrollment (id SERIAL NOT NULL, employee_id INT NOT NULL, event_session_id INT NOT NULL, status_id INT NOT NULL, enrollment_date TIMESTAMP(0) WITH TIME ZONE NOT NULL, completion_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, progress INT NOT NULL, completed BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7725ABBA8C03F15C ON employee_session_enrollment (employee_id)');
        $this->addSql('CREATE INDEX IDX_7725ABBA39D135F0 ON employee_session_enrollment (event_session_id)');
        $this->addSql('CREATE INDEX IDX_7725ABBA6BF700BD ON employee_session_enrollment (status_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_employee_session_enrollment ON employee_session_enrollment (employee_id, event_session_id)');
        $this->addSql('COMMENT ON COLUMN employee_session_enrollment.enrollment_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_session_enrollment.completion_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_session_enrollment.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_session_enrollment.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE employee_session_enrollment_status (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, display_name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, display_order INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A244B0D5E237E06 ON employee_session_enrollment_status (name)');
        $this->addSql('COMMENT ON COLUMN employee_session_enrollment_status.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_session_enrollment_status.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE employee_session_enrollment ADD CONSTRAINT FK_7725ABBA8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_session_enrollment ADD CONSTRAINT FK_7725ABBA39D135F0 FOREIGN KEY (event_session_id) REFERENCES event_sessions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_session_enrollment ADD CONSTRAINT FK_7725ABBA6BF700BD FOREIGN KEY (status_id) REFERENCES employee_session_enrollment_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_event DROP CONSTRAINT fk_d3a307de8c03f15c');
        $this->addSql('ALTER TABLE employee_event DROP CONSTRAINT fk_d3a307de71f7e88b');
        $this->addSql('ALTER TABLE employee_event DROP CONSTRAINT fk_d3a307de6bf700bd');
        $this->addSql('DROP TABLE employee_event_status');
        $this->addSql('DROP TABLE employee_event');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE employee_event_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE employee_event_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE employee_event_status (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, display_name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, display_order INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_67ac390e5e237e06 ON employee_event_status (name)');
        $this->addSql('COMMENT ON COLUMN employee_event_status.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_event_status.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE employee_event (id SERIAL NOT NULL, employee_id INT NOT NULL, event_id INT NOT NULL, status_id INT NOT NULL, enrollment_date TIMESTAMP(0) WITH TIME ZONE NOT NULL, completion_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, progress INT NOT NULL, completed BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX unique_employee_event ON employee_event (employee_id, event_id)');
        $this->addSql('CREATE INDEX idx_d3a307de6bf700bd ON employee_event (status_id)');
        $this->addSql('CREATE INDEX idx_d3a307de71f7e88b ON employee_event (event_id)');
        $this->addSql('CREATE INDEX idx_d3a307de8c03f15c ON employee_event (employee_id)');
        $this->addSql('COMMENT ON COLUMN employee_event.enrollment_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_event.completion_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_event.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_event.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE employee_event ADD CONSTRAINT fk_d3a307de8c03f15c FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_event ADD CONSTRAINT fk_d3a307de71f7e88b FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_event ADD CONSTRAINT fk_d3a307de6bf700bd FOREIGN KEY (status_id) REFERENCES employee_event_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_session_enrollment DROP CONSTRAINT FK_7725ABBA8C03F15C');
        $this->addSql('ALTER TABLE employee_session_enrollment DROP CONSTRAINT FK_7725ABBA39D135F0');
        $this->addSql('ALTER TABLE employee_session_enrollment DROP CONSTRAINT FK_7725ABBA6BF700BD');
        $this->addSql('DROP TABLE employee_session_enrollment');
        $this->addSql('DROP TABLE employee_session_enrollment_status');
    }
}

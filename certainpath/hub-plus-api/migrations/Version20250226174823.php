<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226174823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE course_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE employee_course_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE employee_course_favorite_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE employee_course_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE course_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE course_instructor_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE course_files_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE course_category_id_seq CASCADE');
        $this->addSql('CREATE TABLE employee_event (id SERIAL NOT NULL, employee_id INT NOT NULL, event_id INT NOT NULL, status_id INT NOT NULL, enrollment_date TIMESTAMP(0) WITH TIME ZONE NOT NULL, completion_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, progress INT NOT NULL, completed BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D3A307DE8C03F15C ON employee_event (employee_id)');
        $this->addSql('CREATE INDEX IDX_D3A307DE71F7E88B ON employee_event (event_id)');
        $this->addSql('CREATE INDEX IDX_D3A307DE6BF700BD ON employee_event (status_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_employee_event ON employee_event (employee_id, event_id)');
        $this->addSql('COMMENT ON COLUMN employee_event.enrollment_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_event.completion_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_event.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_event.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE employee_event_favorite (id SERIAL NOT NULL, employee_id INT NOT NULL, event_id INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_72ECFF798C03F15C ON employee_event_favorite (employee_id)');
        $this->addSql('CREATE INDEX IDX_72ECFF7971F7E88B ON employee_event_favorite (event_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_employee_event_favorite ON employee_event_favorite (employee_id, event_id)');
        $this->addSql('COMMENT ON COLUMN employee_event_favorite.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_event_favorite.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE employee_event_status (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, display_name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, display_order INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_67AC390E5E237E06 ON employee_event_status (name)');
        $this->addSql('COMMENT ON COLUMN employee_event_status.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_event_status.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE event (id SERIAL NOT NULL, event_type_id INT DEFAULT NULL, event_category_id INT DEFAULT NULL, event_instructor_id INT DEFAULT NULL, deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, event_code VARCHAR(100) NOT NULL, event_name VARCHAR(255) NOT NULL, event_description TEXT NOT NULL, event_price NUMERIC(18, 2) NOT NULL, hide_from_calendar BOOLEAN NOT NULL, hide_from_catalog BOOLEAN NOT NULL, is_published BOOLEAN DEFAULT NULL, sgi_voucher_value NUMERIC(18, 2) DEFAULT NULL, is_eligible_for_returning_student BOOLEAN DEFAULT NULL, is_voucher_eligible BOOLEAN DEFAULT NULL, docebo_event_id INT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, craft_event_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7401B253C ON event (event_type_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7A816713F ON event (event_instructor_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7B9CF4E62 ON event (event_category_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA768DE7C33 ON event (event_code) WHERE (deleted_at IS NULL)');
        $this->addSql('COMMENT ON COLUMN event.deleted_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE event_category (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_40A0F0115E237E06 ON event_category (name)');
        $this->addSql('COMMENT ON COLUMN event_category.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_category.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE event_files (id SERIAL NOT NULL, event_id INT NOT NULL, file_url VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, original_file_name VARCHAR(255) NOT NULL, file_type VARCHAR(50) NOT NULL, mime_type VARCHAR(100) DEFAULT NULL, file_size INT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_472EF17571F7E88B ON event_files (event_id)');
        $this->addSql('COMMENT ON COLUMN event_files.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_files.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE event_instructor (id SERIAL NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_69129AF2E7927C74 ON event_instructor (email)');
        $this->addSql('COMMENT ON COLUMN event_instructor.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_instructor.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE event_type (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_93151B825E237E06 ON event_type (name)');
        $this->addSql('COMMENT ON COLUMN event_type.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_type.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE employee_event ADD CONSTRAINT FK_D3A307DE8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_event ADD CONSTRAINT FK_D3A307DE71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_event ADD CONSTRAINT FK_D3A307DE6BF700BD FOREIGN KEY (status_id) REFERENCES employee_event_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_event_favorite ADD CONSTRAINT FK_72ECFF798C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_event_favorite ADD CONSTRAINT FK_72ECFF7971F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7401B253C FOREIGN KEY (event_type_id) REFERENCES event_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A816713F FOREIGN KEY (event_instructor_id) REFERENCES event_instructor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7B9CF4E62 FOREIGN KEY (event_category_id) REFERENCES event_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_files ADD CONSTRAINT FK_472EF17571F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE course DROP CONSTRAINT fk_169e6fb9cd8f897f');
        $this->addSql('ALTER TABLE course DROP CONSTRAINT fk_169e6fb94fc2854a');
        $this->addSql('ALTER TABLE course DROP CONSTRAINT fk_169e6fb96628ad36');
        $this->addSql('ALTER TABLE employee_course DROP CONSTRAINT fk_3fafab2c8c03f15c');
        $this->addSql('ALTER TABLE employee_course DROP CONSTRAINT fk_3fafab2c591cc992');
        $this->addSql('ALTER TABLE employee_course DROP CONSTRAINT fk_3fafab2c6bf700bd');
        $this->addSql('ALTER TABLE employee_course_favorite DROP CONSTRAINT fk_9128572b8c03f15c');
        $this->addSql('ALTER TABLE employee_course_favorite DROP CONSTRAINT fk_9128572b591cc992');
        $this->addSql('ALTER TABLE course_files DROP CONSTRAINT fk_ef9e47b1591cc992');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE course_category');
        $this->addSql('DROP TABLE course_instructor');
        $this->addSql('DROP TABLE course_type');
        $this->addSql('DROP TABLE employee_course');
        $this->addSql('DROP TABLE employee_course_status');
        $this->addSql('DROP TABLE employee_course_favorite');
        $this->addSql('DROP TABLE course_files');

        $this->addSql('CREATE TABLE event_sessions (id SERIAL NOT NULL, event_id INT NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, max_enrollments INT NOT NULL, virtual_link VARCHAR(255) DEFAULT NULL, notes TEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_33D4F045591CC992 ON event_sessions (event_id)');
        $this->addSql('COMMENT ON COLUMN event_sessions.start_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_sessions.end_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_sessions.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_sessions.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE event_sessions ADD CONSTRAINT FK_33D4F045591CC992 FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE course_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE employee_course_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE employee_course_favorite_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE employee_course_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE course_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE course_instructor_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE course_files_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE course_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE course (id SERIAL NOT NULL, course_type_id INT DEFAULT NULL, course_category_id INT DEFAULT NULL, course_instructor_id INT DEFAULT NULL, deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, course_code VARCHAR(100) NOT NULL, course_name VARCHAR(255) NOT NULL, course_description TEXT NOT NULL, course_price NUMERIC(18, 2) NOT NULL, hide_from_calendar BOOLEAN NOT NULL, hide_from_catalog BOOLEAN NOT NULL, is_published BOOLEAN DEFAULT NULL, sgi_voucher_value NUMERIC(18, 2) DEFAULT NULL, is_eligible_for_returning_student BOOLEAN DEFAULT NULL, is_voucher_eligible BOOLEAN DEFAULT NULL, docebo_course_id INT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, craft_course_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_169e6fb96628ad36 ON course (course_category_id)');
        $this->addSql('CREATE INDEX idx_169e6fb94fc2854a ON course (course_instructor_id)');
        $this->addSql('CREATE INDEX idx_169e6fb9cd8f897f ON course (course_type_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_169e6fb9bfb7ed9e ON course (course_code) WHERE (deleted_at IS NULL)');
        $this->addSql('COMMENT ON COLUMN course.deleted_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN course.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN course.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE course_category (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_aff874975e237e06 ON course_category (name)');
        $this->addSql('COMMENT ON COLUMN course_category.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN course_category.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE course_instructor (id SERIAL NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_ee1e0d5ee7927c74 ON course_instructor (email)');
        $this->addSql('COMMENT ON COLUMN course_instructor.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN course_instructor.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE course_type (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_447c8a2f5e237e06 ON course_type (name)');
        $this->addSql('COMMENT ON COLUMN course_type.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN course_type.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE employee_course (id SERIAL NOT NULL, employee_id INT NOT NULL, course_id INT NOT NULL, status_id INT NOT NULL, enrollment_date TIMESTAMP(0) WITH TIME ZONE NOT NULL, completion_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, progress INT NOT NULL, completed BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX unique_employee_course ON employee_course (employee_id, course_id)');
        $this->addSql('CREATE INDEX idx_3fafab2c6bf700bd ON employee_course (status_id)');
        $this->addSql('CREATE INDEX idx_3fafab2c591cc992 ON employee_course (course_id)');
        $this->addSql('CREATE INDEX idx_3fafab2c8c03f15c ON employee_course (employee_id)');
        $this->addSql('COMMENT ON COLUMN employee_course.enrollment_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_course.completion_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_course.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_course.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE employee_course_status (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, display_name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, display_order INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_a3cad0ea5e237e06 ON employee_course_status (name)');
        $this->addSql('COMMENT ON COLUMN employee_course_status.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_course_status.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE employee_course_favorite (id SERIAL NOT NULL, employee_id INT NOT NULL, course_id INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX unique_employee_course_favorite ON employee_course_favorite (employee_id, course_id)');
        $this->addSql('CREATE INDEX idx_9128572b591cc992 ON employee_course_favorite (course_id)');
        $this->addSql('CREATE INDEX idx_9128572b8c03f15c ON employee_course_favorite (employee_id)');
        $this->addSql('COMMENT ON COLUMN employee_course_favorite.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_course_favorite.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE course_files (id SERIAL NOT NULL, course_id INT NOT NULL, file_url VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, original_file_name VARCHAR(255) NOT NULL, file_type VARCHAR(50) NOT NULL, mime_type VARCHAR(100) DEFAULT NULL, file_size INT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_ef9e47b1591cc992 ON course_files (course_id)');
        $this->addSql('COMMENT ON COLUMN course_files.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN course_files.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT fk_169e6fb9cd8f897f FOREIGN KEY (course_type_id) REFERENCES course_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT fk_169e6fb9cd8f897f FOREIGN KEY (course_type_id) REFERENCES course_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT fk_169e6fb94fc2854a FOREIGN KEY (course_instructor_id) REFERENCES course_instructor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT fk_169e6fb96628ad36 FOREIGN KEY (course_category_id) REFERENCES course_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_course ADD CONSTRAINT fk_3fafab2c8c03f15c FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_course ADD CONSTRAINT fk_3fafab2c591cc992 FOREIGN KEY (course_id) REFERENCES course (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_course ADD CONSTRAINT fk_3fafab2c6bf700bd FOREIGN KEY (status_id) REFERENCES employee_course_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_course_favorite ADD CONSTRAINT fk_9128572b8c03f15c FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_course_favorite ADD CONSTRAINT fk_9128572b591cc992 FOREIGN KEY (course_id) REFERENCES course (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE course_files ADD CONSTRAINT fk_ef9e47b1591cc992 FOREIGN KEY (course_id) REFERENCES course (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_event DROP CONSTRAINT FK_D3A307DE8C03F15C');
        $this->addSql('ALTER TABLE employee_event DROP CONSTRAINT FK_D3A307DE71F7E88B');
        $this->addSql('ALTER TABLE employee_event DROP CONSTRAINT FK_D3A307DE6BF700BD');
        $this->addSql('ALTER TABLE employee_event_favorite DROP CONSTRAINT FK_72ECFF798C03F15C');
        $this->addSql('ALTER TABLE employee_event_favorite DROP CONSTRAINT FK_72ECFF7971F7E88B');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7401B253C');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7A816713F');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7B9CF4E62');
        $this->addSql('ALTER TABLE event_files DROP CONSTRAINT FK_472EF17571F7E88B');
        $this->addSql('DROP TABLE employee_event');
        $this->addSql('DROP TABLE employee_event_favorite');
        $this->addSql('DROP TABLE employee_event_status');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_category');
        $this->addSql('DROP TABLE event_files');
        $this->addSql('DROP TABLE event_instructor');
        $this->addSql('DROP TABLE event_type');
    }
}

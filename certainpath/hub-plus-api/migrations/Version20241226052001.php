<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241226052001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE course (id SERIAL NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, course_code VARCHAR(100) NOT NULL, course_name VARCHAR(255) NOT NULL, course_description TEXT NOT NULL, course_price NUMERIC(18, 2) NOT NULL, course_type_id INT DEFAULT NULL, course_category_id INT DEFAULT NULL, hide_from_calendar BOOLEAN NOT NULL, hide_from_catalog BOOLEAN NOT NULL, is_published BOOLEAN DEFAULT NULL, sgi_voucher_value NUMERIC(18, 2) DEFAULT NULL, is_eligible_for_returning_student BOOLEAN DEFAULT NULL, is_voucher_eligible BOOLEAN DEFAULT NULL, docebo_course_id INT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, craft_course_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_169E6FB9BFB7ED9E ON course (course_code)');
        $this->addSql('CREATE TABLE employee_course (id SERIAL NOT NULL, employee_id INT NOT NULL, course_id INT NOT NULL, status_id INT NOT NULL, enrollment_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, completion_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, progress INT NOT NULL, completed BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3FAFAB2C8C03F15C ON employee_course (employee_id)');
        $this->addSql('CREATE INDEX IDX_3FAFAB2C591CC992 ON employee_course (course_id)');
        $this->addSql('CREATE INDEX IDX_3FAFAB2C6BF700BD ON employee_course (status_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_employee_course ON employee_course (employee_id, course_id)');
        $this->addSql('CREATE TABLE employee_course_favorite (id SERIAL NOT NULL, employee_id INT NOT NULL, course_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9128572B8C03F15C ON employee_course_favorite (employee_id)');
        $this->addSql('CREATE INDEX IDX_9128572B591CC992 ON employee_course_favorite (course_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_employee_course_favorite ON employee_course_favorite (employee_id, course_id)');
        $this->addSql('CREATE TABLE employee_course_status (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, display_name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, display_order INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3CAD0EA5E237E06 ON employee_course_status (name)');
        $this->addSql('ALTER TABLE employee_course ADD CONSTRAINT FK_3FAFAB2C8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_course ADD CONSTRAINT FK_3FAFAB2C591CC992 FOREIGN KEY (course_id) REFERENCES course (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_course ADD CONSTRAINT FK_3FAFAB2C6BF700BD FOREIGN KEY (status_id) REFERENCES employee_course_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_course_favorite ADD CONSTRAINT FK_9128572B8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_course_favorite ADD CONSTRAINT FK_9128572B591CC992 FOREIGN KEY (course_id) REFERENCES course (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE employee_course DROP CONSTRAINT FK_3FAFAB2C8C03F15C');
        $this->addSql('ALTER TABLE employee_course DROP CONSTRAINT FK_3FAFAB2C591CC992');
        $this->addSql('ALTER TABLE employee_course DROP CONSTRAINT FK_3FAFAB2C6BF700BD');
        $this->addSql('ALTER TABLE employee_course_favorite DROP CONSTRAINT FK_9128572B8C03F15C');
        $this->addSql('ALTER TABLE employee_course_favorite DROP CONSTRAINT FK_9128572B591CC992');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE employee_course');
        $this->addSql('DROP TABLE employee_course_favorite');
        $this->addSql('DROP TABLE employee_course_status');
    }
}

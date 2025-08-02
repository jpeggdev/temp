<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211200333 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE course_category (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AFF874975E237E06 ON course_category (name)');
        $this->addSql('COMMENT ON COLUMN course_category.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN course_category.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB96628AD36 FOREIGN KEY (course_category_id) REFERENCES course_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_169E6FB96628AD36 ON course (course_category_id)');

        // Insert initial course categories
        $this->addSql("INSERT INTO course_category (name, description, is_active, created_at, updated_at) VALUES
            ('Admin & Reporting', 'Courses focused on administrative tasks and reporting skills', true, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
            ('Call Center', 'Training materials for call center operations and customer service', true, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
            ('Executive Perspective', 'Strategic insights and leadership courses for executives', true, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
            ('Leadership', 'Development courses for current and aspiring leaders', true, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
            ('Marketing', 'Courses covering marketing strategies and techniques', true, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
            ('Operations', 'Training for operational processes and management', true, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
            ('PATH Training', 'Professional development and career path training courses', true, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
            ('People Management', 'Courses focused on managing and developing teams', true, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
            ('Sales', 'Training materials for sales techniques and strategies', true, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course DROP CONSTRAINT FK_169E6FB96628AD36');
        $this->addSql('DROP TABLE course_category');
        $this->addSql('DROP INDEX IDX_169E6FB96628AD36');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250309205522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE employee_role (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E2B0C02D5E237E06 ON employee_role (name)');
        $this->addSql('CREATE TABLE resource (id SERIAL NOT NULL, type_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, tagline VARCHAR(255) DEFAULT NULL, publish_start_date TIMESTAMP(0) WITH TIME ZONE NOT NULL, publish_end_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, content_url VARCHAR(255) DEFAULT NULL, thumbnail_url VARCHAR(255) DEFAULT NULL, is_published BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BC91F416C54C8C93 ON resource (type_id)');
        $this->addSql('CREATE TABLE resource_category (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A8C0D36C5E237E06 ON resource_category (name)');
        $this->addSql('CREATE TABLE resource_category_mapping (id SERIAL NOT NULL, resource_id INT NOT NULL, resource_category_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_717EAB3889329D25 ON resource_category_mapping (resource_id)');
        $this->addSql('CREATE INDEX IDX_717EAB3816FDA3B0 ON resource_category_mapping (resource_category_id)');
        $this->addSql('CREATE TABLE resource_content_block (id SERIAL NOT NULL, resource_id INT NOT NULL, content TEXT NOT NULL, type VARCHAR(255) NOT NULL, sort_order INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2134E53689329D25 ON resource_content_block (resource_id)');
        $this->addSql('CREATE TABLE resource_employee_role_mapping (id SERIAL NOT NULL, resource_id INT NOT NULL, employee_role_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FFDF01E689329D25 ON resource_employee_role_mapping (resource_id)');
        $this->addSql('CREATE INDEX IDX_FFDF01E6564F74A3 ON resource_employee_role_mapping (employee_role_id)');
        $this->addSql('CREATE TABLE resource_tag (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_23D039CA5E237E06 ON resource_tag (name)');
        $this->addSql('CREATE TABLE resource_tag_mapping (id SERIAL NOT NULL, resource_id INT NOT NULL, resource_tag_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7F6C3F7289329D25 ON resource_tag_mapping (resource_id)');
        $this->addSql('CREATE INDEX IDX_7F6C3F72722E89EB ON resource_tag_mapping (resource_tag_id)');
        $this->addSql('CREATE TABLE resource_trade_mapping (id SERIAL NOT NULL, resource_id INT NOT NULL, trade_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F38A52BA89329D25 ON resource_trade_mapping (resource_id)');
        $this->addSql('CREATE INDEX IDX_F38A52BAC2D9760 ON resource_trade_mapping (trade_id)');
        $this->addSql('CREATE TABLE resource_type (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_83FEF7935E237E06 ON resource_type (name)');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F416C54C8C93 FOREIGN KEY (type_id) REFERENCES resource_type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource_category_mapping ADD CONSTRAINT FK_717EAB3889329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource_category_mapping ADD CONSTRAINT FK_717EAB3816FDA3B0 FOREIGN KEY (resource_category_id) REFERENCES resource_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource_content_block ADD CONSTRAINT FK_2134E53689329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource_employee_role_mapping ADD CONSTRAINT FK_FFDF01E689329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource_employee_role_mapping ADD CONSTRAINT FK_FFDF01E6564F74A3 FOREIGN KEY (employee_role_id) REFERENCES employee_role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource_tag_mapping ADD CONSTRAINT FK_7F6C3F7289329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource_tag_mapping ADD CONSTRAINT FK_7F6C3F72722E89EB FOREIGN KEY (resource_tag_id) REFERENCES resource_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource_trade_mapping ADD CONSTRAINT FK_F38A52BA89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource_trade_mapping ADD CONSTRAINT FK_F38A52BAC2D9760 FOREIGN KEY (trade_id) REFERENCES trade (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7E1A43665E237E06 ON trade (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource DROP CONSTRAINT FK_BC91F416C54C8C93');
        $this->addSql('ALTER TABLE resource_category_mapping DROP CONSTRAINT FK_717EAB3889329D25');
        $this->addSql('ALTER TABLE resource_category_mapping DROP CONSTRAINT FK_717EAB3816FDA3B0');
        $this->addSql('ALTER TABLE resource_content_block DROP CONSTRAINT FK_2134E53689329D25');
        $this->addSql('ALTER TABLE resource_employee_role_mapping DROP CONSTRAINT FK_FFDF01E689329D25');
        $this->addSql('ALTER TABLE resource_employee_role_mapping DROP CONSTRAINT FK_FFDF01E6564F74A3');
        $this->addSql('ALTER TABLE resource_tag_mapping DROP CONSTRAINT FK_7F6C3F7289329D25');
        $this->addSql('ALTER TABLE resource_tag_mapping DROP CONSTRAINT FK_7F6C3F72722E89EB');
        $this->addSql('ALTER TABLE resource_trade_mapping DROP CONSTRAINT FK_F38A52BA89329D25');
        $this->addSql('ALTER TABLE resource_trade_mapping DROP CONSTRAINT FK_F38A52BAC2D9760');
        $this->addSql('DROP TABLE employee_role');
        $this->addSql('DROP TABLE resource');
        $this->addSql('DROP TABLE resource_category');
        $this->addSql('DROP TABLE resource_category_mapping');
        $this->addSql('DROP TABLE resource_content_block');
        $this->addSql('DROP TABLE resource_employee_role_mapping');
        $this->addSql('DROP TABLE resource_tag');
        $this->addSql('DROP TABLE resource_tag_mapping');
        $this->addSql('DROP TABLE resource_trade_mapping');
        $this->addSql('DROP TABLE resource_type');
        $this->addSql('DROP INDEX UNIQ_7E1A43665E237E06');
    }
}

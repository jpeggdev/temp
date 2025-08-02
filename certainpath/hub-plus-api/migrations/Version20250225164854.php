<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225164854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE company_field_service_import_id_seq CASCADE');
        $this->addSql('CREATE TABLE company_data_import_job (id SERIAL NOT NULL, company_id INT DEFAULT NULL, is_jobs_or_invoice_file BOOLEAN DEFAULT false NOT NULL, is_active_club_member_file BOOLEAN DEFAULT false NOT NULL, is_member_file BOOLEAN DEFAULT false NOT NULL, trade VARCHAR(255) NOT NULL, software VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, progress VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EF7140DC979B1AD6 ON company_data_import_job (company_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EF7140DCD17F50A6 ON company_data_import_job (uuid)');
        $this->addSql('COMMENT ON COLUMN company_data_import_job.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN company_data_import_job.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE company_data_import_job ADD CONSTRAINT FK_EF7140DC979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE company_field_service_import DROP CONSTRAINT fk_ec914599979b1ad6');
        $this->addSql('DROP TABLE company_field_service_import');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE company_field_service_import_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE company_field_service_import (id SERIAL NOT NULL, company_id INT DEFAULT NULL, is_jobs_or_invoice_file BOOLEAN DEFAULT false NOT NULL, is_active_club_member_file BOOLEAN DEFAULT false NOT NULL, is_member_file BOOLEAN DEFAULT false NOT NULL, trade VARCHAR(255) NOT NULL, software VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, progress VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_ec914599d17f50a6 ON company_field_service_import (uuid)');
        $this->addSql('CREATE INDEX idx_ec914599979b1ad6 ON company_field_service_import (company_id)');
        $this->addSql('COMMENT ON COLUMN company_field_service_import.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN company_field_service_import.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE company_field_service_import ADD CONSTRAINT fk_ec914599979b1ad6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE company_data_import_job DROP CONSTRAINT FK_EF7140DC979B1AD6');
        $this->addSql('DROP TABLE company_data_import_job');
    }
}

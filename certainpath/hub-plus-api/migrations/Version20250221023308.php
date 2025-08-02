<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221023308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE company_field_service_import (id SERIAL NOT NULL, company_id INT DEFAULT NULL, is_jobs_or_invoice_file BOOLEAN NOT NULL, is_active_club_member_file BOOLEAN NOT NULL, is_member_file BOOLEAN NOT NULL, trade VARCHAR(255) NOT NULL, software VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, progress VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EC914599979B1AD6 ON company_field_service_import (company_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EC914599D17F50A6 ON company_field_service_import (uuid)');
        $this->addSql('ALTER TABLE company_field_service_import ADD CONSTRAINT FK_EC914599979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_field_service_import DROP CONSTRAINT FK_EC914599979B1AD6');
        $this->addSql('DROP TABLE company_field_service_import');
    }
}

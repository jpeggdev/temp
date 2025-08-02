<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225165645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_data_import_job ADD is_prospects_file BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE company_data_import_job ADD data_source VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_data_import_job DROP is_prospects_file');
        $this->addSql('ALTER TABLE company_data_import_job DROP data_source');
    }
}

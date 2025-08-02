<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225192403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_data_import_job ADD intacct_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_EF7140DC72AD9E41 ON company_data_import_job (intacct_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_EF7140DC72AD9E41');
        $this->addSql('ALTER TABLE company_data_import_job DROP intacct_id');
    }
}

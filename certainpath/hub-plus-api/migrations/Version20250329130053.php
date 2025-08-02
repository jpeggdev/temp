<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329130053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5D9F75A1D17F50A6 ON employee (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6E60EB86D17F50A6 ON field_service_export (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34013B4D17F50A6 ON field_service_export_attachment (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8C9F3610D17F50A6 ON file (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3ACF3D1D17F50A6 ON file_tmp (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D9380EE6D17F50A6 ON quickbooks_report (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC91F416D17F50A6 ON resource (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2134E536D17F50A6 ON resource_content_block (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_D34013B4D17F50A6');
        $this->addSql('DROP INDEX UNIQ_8C9F3610D17F50A6');
        $this->addSql('DROP INDEX UNIQ_3ACF3D1D17F50A6');
        $this->addSql('DROP INDEX UNIQ_BC91F416D17F50A6');
        $this->addSql('DROP INDEX UNIQ_5D9F75A1D17F50A6');
        $this->addSql('DROP INDEX UNIQ_2134E536D17F50A6');
        $this->addSql('DROP INDEX UNIQ_D9380EE6D17F50A6');
        $this->addSql('DROP INDEX UNIQ_6E60EB86D17F50A6');
    }
}

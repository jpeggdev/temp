<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221024139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_field_service_import ALTER is_jobs_or_invoice_file SET DEFAULT false');
        $this->addSql('ALTER TABLE company_field_service_import ALTER is_active_club_member_file SET DEFAULT false');
        $this->addSql('ALTER TABLE company_field_service_import ALTER is_member_file SET DEFAULT false');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_field_service_import ALTER is_jobs_or_invoice_file DROP DEFAULT');
        $this->addSql('ALTER TABLE company_field_service_import ALTER is_active_club_member_file DROP DEFAULT');
        $this->addSql('ALTER TABLE company_field_service_import ALTER is_member_file DROP DEFAULT');
    }
}

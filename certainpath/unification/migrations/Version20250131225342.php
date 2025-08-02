<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250131225342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates indexes for external_id columns and more';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX address_address_external_id_idx ON address (external_id)');
        $this->addSql('CREATE INDEX address_company_address_external_id_idx ON address (company_id, external_id)');
        $this->addSql('CREATE INDEX business_unit_company_external_id_idx ON business_unit (company_id, external_id)');
        $this->addSql('CREATE INDEX business_unit_external_id_idx ON business_unit (external_id)');
        $this->addSql('CREATE INDEX company_company_identifier_idx ON company (identifier)');
        $this->addSql('CREATE INDEX email_company_external_id_idx ON email (company_id, external_id)');
        $this->addSql('CREATE INDEX email_external_id_idx ON email (external_id)');
        $this->addSql('CREATE INDEX invoice_external_id_idx ON invoice (external_id)');
        $this->addSql('CREATE INDEX invoice_company_customer_invoiceNumber_idx
                            ON invoice (company_id, customer_id, invoice_number)');
        $this->addSql('CREATE INDEX location_company_external_id_idx ON location (company_id, external_id)');
        $this->addSql('CREATE INDEX location_external_id_idx ON location (external_id)');
        $this->addSql('CREATE INDEX mail_package_external_id_idx ON mail_package (external_id)');
        $this->addSql('CREATE INDEX phone_company_external_id_idx ON phone (company_id, external_id)');
        $this->addSql('CREATE INDEX phone_external_id_idx ON phone (external_id)');
        $this->addSql('CREATE INDEX prospect_external_id_idx ON prospect (external_id)');
        $this->addSql('CREATE INDEX restricted_address_address_external_id_idx ON restricted_address (external_id)');
        $this->addSql('CREATE INDEX subscription_company_external_id_idx ON subscription (company_id, external_id)');
        $this->addSql('CREATE INDEX subscription_external_id_idx ON subscription (external_id)');
        $this->addSql('CREATE INDEX tag_company_tag_name_idx ON tag (company_id, name)');
        $this->addSql('CREATE INDEX tag_tag_name_idx ON tag (name)');
    }
    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX address_address_external_id_idx');
        $this->addSql('DROP INDEX address_company_address_external_id_idx');
        $this->addSql('DROP INDEX business_unit_company_external_id_idx');
        $this->addSql('DROP INDEX business_unit_external_id_idx');
        $this->addSql('DROP INDEX company_company_identifier_idx');
        $this->addSql('DROP INDEX email_company_external_id_idx');
        $this->addSql('DROP INDEX email_external_id_idx');
        $this->addSql('DROP INDEX invoice_external_id_idx');
        $this->addSql('DROP INDEX invoice_company_customer_invoiceNumber_idx');
        $this->addSql('DROP INDEX location_company_external_id_idx');
        $this->addSql('DROP INDEX location_external_id_idx');
        $this->addSql('DROP INDEX mail_package_external_id_idx');
        $this->addSql('DROP INDEX phone_company_external_id_idx');
        $this->addSql('DROP INDEX phone_external_id_idx');
        $this->addSql('DROP INDEX prospect_external_id_idx');
        $this->addSql('DROP INDEX restricted_address_address_external_id_idx');
        $this->addSql('DROP INDEX subscription_company_external_id_idx');
        $this->addSql('DROP INDEX subscription_external_id_idx');
        $this->addSql('DROP INDEX tag_company_tag_name_idx');
        $this->addSql('DROP INDEX tag_tag_name_idx');
    }
}

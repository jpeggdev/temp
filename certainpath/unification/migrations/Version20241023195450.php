<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241023195450 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer ADD has_installation BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE customer ADD has_subscription BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE customer ADD legacy_count_invoices INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE customer ADD legacy_first_invoiced_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD legacy_lifetime_value TEXT DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE customer ADD legacy_first_sale_amount TEXT DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE customer ADD legacy_last_invoice_number TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN customer.legacy_first_invoiced_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer DROP has_installation');
        $this->addSql('ALTER TABLE customer DROP has_subscription');
        $this->addSql('ALTER TABLE customer DROP legacy_count_invoices');
        $this->addSql('ALTER TABLE customer DROP legacy_first_invoiced_at');
        $this->addSql('ALTER TABLE customer DROP legacy_lifetime_value');
        $this->addSql('ALTER TABLE customer DROP legacy_first_sale_amount');
        $this->addSql('ALTER TABLE customer DROP legacy_last_invoice_number');
    }
}

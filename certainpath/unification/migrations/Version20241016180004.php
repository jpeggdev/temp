<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241016180004 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer ADD is_new_customer BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE customer ADD is_repeat_customer BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE customer ADD count_invoices INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE customer ADD last_invoiced_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD balance_total TEXT DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE customer ADD invoice_total TEXT DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE customer ADD lifetime_value TEXT DEFAULT \'0.00\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN customer.last_invoiced_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE invoice ALTER total SET DEFAULT \'0.00\'');
        $this->addSql('ALTER TABLE invoice ALTER total SET NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER balance SET DEFAULT \'0.00\'');
        $this->addSql('UPDATE invoice SET balance = \'0.00\' WHERE balance IS NULL');
        $this->addSql('ALTER TABLE invoice ALTER balance SET NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER sub_total SET DEFAULT \'0.00\'');
        $this->addSql('UPDATE invoice SET sub_total = \'0.00\' WHERE sub_total IS NULL');
        $this->addSql('ALTER TABLE invoice ALTER sub_total SET NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER tax SET DEFAULT \'0.00\'');
        $this->addSql('UPDATE invoice SET tax = \'0.00\' WHERE tax IS NULL');
        $this->addSql('ALTER TABLE invoice ALTER tax SET NOT NULL');
        $this->addSql('ALTER TABLE prospect ALTER is_preferred SET DEFAULT true');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE prospect ALTER is_preferred SET DEFAULT false');
        $this->addSql('ALTER TABLE customer DROP is_new_customer');
        $this->addSql('ALTER TABLE customer DROP is_repeat_customer');
        $this->addSql('ALTER TABLE customer DROP count_invoices');
        $this->addSql('ALTER TABLE customer DROP last_invoiced_at');
        $this->addSql('ALTER TABLE customer DROP balance_total');
        $this->addSql('ALTER TABLE customer DROP invoice_total');
        $this->addSql('ALTER TABLE customer DROP lifetime_value');
        $this->addSql('ALTER TABLE invoice ALTER total DROP DEFAULT');
        $this->addSql('ALTER TABLE invoice ALTER total DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER balance DROP DEFAULT');
        $this->addSql('ALTER TABLE invoice ALTER balance DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER sub_total DROP DEFAULT');
        $this->addSql('ALTER TABLE invoice ALTER sub_total DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER tax DROP DEFAULT');
        $this->addSql('ALTER TABLE invoice ALTER tax DROP NOT NULL');
    }
}

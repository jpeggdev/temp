<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241014211222 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX invoice_company_identifier_idx');
        $this->addSql('ALTER TABLE invoice ALTER identifier DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice ALTER identifier SET NOT NULL');
        $this->addSql('CREATE INDEX invoice_company_identifier_idx ON invoice (company_id, identifier)');
    }
}

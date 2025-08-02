<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241011213004 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer DROP external_id');
        $this->addSql('ALTER TABLE customer DROP processed_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer ADD external_id TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN customer.processed_at IS \'(DC2Type:datetime_immutable)\'');
    }
}

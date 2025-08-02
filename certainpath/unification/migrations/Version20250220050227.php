<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250220050227 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer ADD version TEXT DEFAULT NULL');
        $this->addSql('CREATE INDEX customer_version_idx ON customer (version)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX customer_version_idx');
        $this->addSql('ALTER TABLE customer DROP version');
    }
}

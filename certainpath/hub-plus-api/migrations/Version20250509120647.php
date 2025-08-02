<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250509120647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seeds discount_type table with "percentage" and "fixed_amount" rows.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO discount_type (name) VALUES ('percentage'), ('fixed_amount')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM discount_type WHERE name IN ('percentage', 'fixed_amount')");
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241105162810 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE address SET verified_at = NOW()');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE address SET verified_at = NULL');
    }
}

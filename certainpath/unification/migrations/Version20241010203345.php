<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241010203345 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        //Chris Note: Changed to no-op
        //$this->addSql('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    }

    public function down(Schema $schema): void
    {
        //$this->addSql('DROP EXTENSION IF EXISTS pg_trgm');
    }
}

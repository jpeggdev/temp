<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250124170624 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE address
            ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        ');
        $this->addSql('COMMENT ON COLUMN address.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('
            ALTER TABLE restricted_address
            ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        ');
        $this->addSql('COMMENT ON COLUMN restricted_address.deleted_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE address DROP COLUMN IF EXISTS deleted_at');
        $this->addSql('ALTER TABLE restricted_address DROP COLUMN IF EXISTS deleted_at');
    }
}

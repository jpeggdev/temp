<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250718194102 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS uniq_bc91f416fdff2e92
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IF NOT EXISTS  IDX_BC91F416FDFF2E92 ON resource (thumbnail_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS uniq_2134e53693cb796c
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IF NOT EXISTS IDX_2134E53693CB796C ON resource_content_block (file_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_2134E53693CB796C
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_2134e53693cb796c ON resource_content_block (file_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_BC91F416FDFF2E92
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_bc91f416fdff2e92 ON resource (thumbnail_id)
        SQL);
    }
}

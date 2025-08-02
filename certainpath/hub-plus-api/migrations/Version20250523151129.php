<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250523151129 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE event_venue DROP CONSTRAINT fk_d08aaa213fe997de
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS IDX_D08AAA213FE997DE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_venue DROP timezone_id
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE event_venue
            ADD timezone_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_venue
            ADD CONSTRAINT fk_d08aaa213fe997de
            FOREIGN KEY (timezone_id) REFERENCES timezone (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D08AAA213FE997DE ON event_venue (timezone_id)
        SQL);
    }
}

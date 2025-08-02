<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250508173859 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE event_venue
            ADD deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL
        ');
        $this->addSql('COMMENT ON COLUMN event_venue.deleted_at IS \'(DC2Type:datetimetz_immutable)\'');

        $this->addSql('
            ALTER TABLE event_venue
            ADD timezone_id INT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE event_venue
            DROP timezone
        ');
        $this->addSql('
            ALTER TABLE event_venue
            ADD CONSTRAINT FK_D08AAA213FE997DE
            FOREIGN KEY (timezone_id)
            REFERENCES timezone (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE event_venue
            DROP deleted_at
        ');
        $this->addSql('
            ALTER TABLE event_venue
            DROP CONSTRAINT IF EXISTS FK_D08AAA213FE997DE
        ');
        $this->addSql('
            DROP INDEX IF EXISTS IDX_D08AAA213FE997DE
        ');
        $this->addSql('
            ALTER TABLE event_venue
            ADD timezone TEXT NOT NULL
        ');
        $this->addSql('
            ALTER TABLE event_venue
            DROP timezone_id
        ');
    }
}

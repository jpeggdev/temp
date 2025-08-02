<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250418114105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE event_instructor_id_seq CASCADE');
        $this->addSql('DROP TABLE event_instructor');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA768DE7C33');
        $this->addSql('ALTER TABLE event ADD thumbnail_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD uuid UUID NOT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7FDFF2E92 FOREIGN KEY (thumbnail_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7FDFF2E92 ON event (thumbnail_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7D17F50A6 ON event (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA768DE7C33 ON event (event_code)');
        $this->addSql('ALTER TABLE event_files DROP CONSTRAINT FK_472EF17571F7E88B');
        $this->addSql('ALTER TABLE event_files DROP CONSTRAINT FK_472EF17593CB796C');
        $this->addSql('ALTER TABLE event_files ADD CONSTRAINT FK_472EF17571F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_files ADD CONSTRAINT FK_472EF17593CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE event_instructor_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE event_instructor (id SERIAL NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_69129af2e7927c74 ON event_instructor (email)');
        $this->addSql('COMMENT ON COLUMN event_instructor.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_instructor.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE event_files DROP CONSTRAINT fk_472ef17571f7e88b');
        $this->addSql('ALTER TABLE event_files DROP CONSTRAINT fk_472ef17593cb796c');
        $this->addSql('ALTER TABLE event_files ADD CONSTRAINT fk_472ef17571f7e88b FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_files ADD CONSTRAINT fk_472ef17593cb796c FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7FDFF2E92');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA7FDFF2E92');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA7D17F50A6');
        $this->addSql('DROP INDEX uniq_3bae0aa768de7c33');
        $this->addSql('ALTER TABLE event DROP thumbnail_id');
        $this->addSql('ALTER TABLE event DROP uuid');
        $this->addSql('CREATE UNIQUE INDEX uniq_3bae0aa768de7c33 ON event (event_code) WHERE (deleted_at IS NULL)');
    }
}

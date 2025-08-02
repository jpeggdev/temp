<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250419144119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_favorite (id SERIAL NOT NULL, event_id INT NOT NULL, employee_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2E29670971F7E88B ON event_favorite (event_id)');
        $this->addSql('CREATE INDEX IDX_2E2967098C03F15C ON event_favorite (employee_id)');
        $this->addSql('COMMENT ON COLUMN event_favorite.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_favorite.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE event_favorite ADD CONSTRAINT FK_2E29670971F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_favorite ADD CONSTRAINT FK_2E2967098C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_favorite DROP CONSTRAINT FK_2E29670971F7E88B');
        $this->addSql('ALTER TABLE event_favorite DROP CONSTRAINT FK_2E2967098C03F15C');
        $this->addSql('DROP TABLE event_favorite');
    }
}

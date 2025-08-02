<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250324153102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE resource_favorite (id SERIAL NOT NULL, resource_id INT NOT NULL, employee_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C649447489329D25 ON resource_favorite (resource_id)');
        $this->addSql('CREATE INDEX IDX_C64944748C03F15C ON resource_favorite (employee_id)');
        $this->addSql('COMMENT ON COLUMN resource_favorite.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN resource_favorite.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE resource_favorite ADD CONSTRAINT FK_C649447489329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource_favorite ADD CONSTRAINT FK_C64944748C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE resource ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('COMMENT ON COLUMN resource.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN resource.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource_favorite DROP CONSTRAINT FK_C649447489329D25');
        $this->addSql('ALTER TABLE resource_favorite DROP CONSTRAINT FK_C64944748C03F15C');
        $this->addSql('DROP TABLE resource_favorite');
        $this->addSql('ALTER TABLE resource ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE resource ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN resource.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN resource.updated_at IS NULL');
    }
}

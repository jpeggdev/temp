<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329125408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE resource_relation (id SERIAL NOT NULL, resource_id INT NOT NULL, related_resource_id INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CC058DE489329D25 ON resource_relation (resource_id)');
        $this->addSql('CREATE INDEX IDX_CC058DE442D9E3A1 ON resource_relation (related_resource_id)');
        $this->addSql('COMMENT ON COLUMN resource_relation.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN resource_relation.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE resource_relation ADD CONSTRAINT FK_CC058DE489329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource_relation ADD CONSTRAINT FK_CC058DE442D9E3A1 FOREIGN KEY (related_resource_id) REFERENCES resource (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource_relation DROP CONSTRAINT FK_CC058DE489329D25');
        $this->addSql('ALTER TABLE resource_relation DROP CONSTRAINT FK_CC058DE442D9E3A1');
        $this->addSql('DROP TABLE resource_relation');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250311125242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource ADD thumbnail_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F416FDFF2E92 FOREIGN KEY (thumbnail_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC91F416FDFF2E92 ON resource (thumbnail_id)');
        $this->addSql('ALTER TABLE resource_content_block ADD file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resource_content_block ADD CONSTRAINT FK_2134E53693CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2134E53693CB796C ON resource_content_block (file_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource_content_block DROP CONSTRAINT FK_2134E53693CB796C');
        $this->addSql('DROP INDEX UNIQ_2134E53693CB796C');
        $this->addSql('ALTER TABLE resource_content_block DROP file_id');
        $this->addSql('ALTER TABLE resource DROP CONSTRAINT FK_BC91F416FDFF2E92');
        $this->addSql('DROP INDEX UNIQ_BC91F416FDFF2E92');
        $this->addSql('ALTER TABLE resource DROP thumbnail_id');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250403152635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_files ADD file_id INT NOT NULL');
        $this->addSql('ALTER TABLE event_files DROP file_url');
        $this->addSql('ALTER TABLE event_files DROP file_name');
        $this->addSql('ALTER TABLE event_files DROP original_file_name');
        $this->addSql('ALTER TABLE event_files DROP mime_type');
        $this->addSql('ALTER TABLE event_files DROP file_size');
        $this->addSql('ALTER TABLE event_files DROP bucket_name');
        $this->addSql('ALTER TABLE event_files ADD CONSTRAINT FK_472EF17593CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_472EF17593CB796C ON event_files (file_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_files DROP CONSTRAINT FK_472EF17593CB796C');
        $this->addSql('DROP INDEX IDX_472EF17593CB796C');
        $this->addSql('ALTER TABLE event_files ADD file_url VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event_files ADD file_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event_files ADD original_file_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event_files ADD mime_type VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE event_files ADD file_size INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_files ADD bucket_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event_files DROP file_id');
    }
}

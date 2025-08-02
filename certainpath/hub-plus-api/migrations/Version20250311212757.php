<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250311212757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE file ADD mime_type VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD file_size INT DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE resource_type ADD is_default BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource_type DROP is_default');
        $this->addSql('ALTER TABLE file DROP mime_type');
        $this->addSql('ALTER TABLE file DROP file_size');
        $this->addSql('ALTER TABLE file DROP url');
    }
}

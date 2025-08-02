<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250310143404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE file (id SERIAL NOT NULL, original_filename VARCHAR(255) NOT NULL, bucket_name VARCHAR(255) NOT NULL, object_key TEXT NOT NULL, content_type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE file_tmp (id SERIAL NOT NULL, file_id INT NOT NULL, is_commited BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3ACF3D193CB796C ON file_tmp (file_id)');
        $this->addSql('ALTER TABLE file_tmp ADD CONSTRAINT FK_3ACF3D193CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE file_tmp DROP CONSTRAINT FK_3ACF3D193CB796C');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE file_tmp');
    }
}

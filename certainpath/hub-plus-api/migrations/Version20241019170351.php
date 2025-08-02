<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241019170351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE field_service_export_attachment (id SERIAL NOT NULL, field_service_export_id INT NOT NULL, original_filename VARCHAR(255) NOT NULL, bucket_name VARCHAR(255) NOT NULL, object_key TEXT NOT NULL, content_type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D34013B473C1E913 ON field_service_export_attachment (field_service_export_id)');
        $this->addSql('ALTER TABLE field_service_export_attachment ADD CONSTRAINT FK_D34013B473C1E913 FOREIGN KEY (field_service_export_id) REFERENCES field_service_export (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE field_service_export ADD from_email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE field_service_export ADD to_email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE field_service_export ADD subject TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE field_service_export ADD email_text TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE field_service_export ADD email_html TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE field_service_export DROP bucket_name');
        $this->addSql('ALTER TABLE field_service_export DROP object_key');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE field_service_export_attachment DROP CONSTRAINT FK_D34013B473C1E913');
        $this->addSql('DROP TABLE field_service_export_attachment');
        $this->addSql('ALTER TABLE field_service_export ADD bucket_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE field_service_export ADD object_key TEXT NOT NULL');
        $this->addSql('ALTER TABLE field_service_export DROP from_email');
        $this->addSql('ALTER TABLE field_service_export DROP to_email');
        $this->addSql('ALTER TABLE field_service_export DROP subject');
        $this->addSql('ALTER TABLE field_service_export DROP email_text');
        $this->addSql('ALTER TABLE field_service_export DROP email_html');
    }
}

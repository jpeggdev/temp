<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241019164808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE field_service_export (id SERIAL NOT NULL, intacct_id VARCHAR(255) DEFAULT NULL, bucket_name VARCHAR(255) NOT NULL, object_key TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6E60EB8672AD9E41 ON field_service_export (intacct_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE field_service_export');
    }
}

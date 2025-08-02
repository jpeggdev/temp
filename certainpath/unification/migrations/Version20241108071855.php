<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241108071855 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE campaign_file_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE file_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE campaign_file (id INT NOT NULL, file_id INT NOT NULL, campaign_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D780DF293CB796C ON campaign_file (file_id)');
        $this->addSql('CREATE INDEX IDX_8D780DF2F639F774 ON campaign_file (campaign_id)');
        $this->addSql('COMMENT ON COLUMN campaign_file.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN campaign_file.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE file (id INT NOT NULL, original_filename VARCHAR(255) NOT NULL, bucket_name VARCHAR(255) NOT NULL, object_key TEXT NOT NULL, content_type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN file.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN file.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE campaign_file ADD CONSTRAINT FK_8D780DF293CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE campaign_file ADD CONSTRAINT FK_8D780DF2F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE campaign_file_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE file_id_seq CASCADE');
        $this->addSql('ALTER TABLE campaign_file DROP CONSTRAINT FK_8D780DF293CB796C');
        $this->addSql('ALTER TABLE campaign_file DROP CONSTRAINT FK_8D780DF2F639F774');
        $this->addSql('DROP TABLE campaign_file');
        $this->addSql('DROP TABLE file');
    }
}

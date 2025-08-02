<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250702173412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP SEQUENCE file_tag_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE file_tag_mapping_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE file_system_node_tag (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE file_system_node_tag_mapping (id SERIAL NOT NULL, file_system_node_tag_id INT NOT NULL, file_system_node_id INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4AF025B4FED56B9 ON file_system_node_tag_mapping (file_system_node_tag_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4AF025B437A15A1F ON file_system_node_tag_mapping (file_system_node_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file_system_node_tag_mapping ADD CONSTRAINT FK_4AF025B4FED56B9 FOREIGN KEY (file_system_node_tag_id) REFERENCES file_system_node_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file_system_node_tag_mapping ADD CONSTRAINT FK_4AF025B437A15A1F FOREIGN KEY (file_system_node_id) REFERENCES filesystem_node (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file_tag_mapping DROP CONSTRAINT fk_596cff4293cb796c
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file_tag_mapping DROP CONSTRAINT fk_596cff422c545ae
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE file_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE file_tag_mapping
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE file_tag_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE file_tag_mapping_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE file_tag (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_2cca391a5e237e06 ON file_tag (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE file_tag_mapping (id SERIAL NOT NULL, file_id INT NOT NULL, file_tag_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_file_tag ON file_tag_mapping (file_id, file_tag_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_596cff422c545ae ON file_tag_mapping (file_tag_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_596cff4293cb796c ON file_tag_mapping (file_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file_tag_mapping ADD CONSTRAINT fk_596cff4293cb796c FOREIGN KEY (file_id) REFERENCES filesystem_node (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file_tag_mapping ADD CONSTRAINT fk_596cff422c545ae FOREIGN KEY (file_tag_id) REFERENCES file_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file_system_node_tag_mapping DROP CONSTRAINT FK_4AF025B4FED56B9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file_system_node_tag_mapping DROP CONSTRAINT FK_4AF025B437A15A1F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE file_system_node_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE file_system_node_tag_mapping
        SQL);
    }
}

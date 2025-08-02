<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250629231535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE file_tag (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_2CCA391A5E237E06 ON file_tag (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE file_tag_mapping (id SERIAL NOT NULL, file_id INT NOT NULL, file_tag_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_596CFF4293CB796C ON file_tag_mapping (file_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_596CFF422C545AE ON file_tag_mapping (file_tag_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_file_tag ON file_tag_mapping (file_id, file_tag_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE folder (id SERIAL NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, path VARCHAR(1024) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_ECA209CD727ACA70 ON folder (parent_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_ECA209CDD17F50A6 ON folder (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_ECA209CDB548B0F ON folder (path)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file_tag_mapping ADD CONSTRAINT FK_596CFF4293CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file_tag_mapping ADD CONSTRAINT FK_596CFF422C545AE FOREIGN KEY (file_tag_id) REFERENCES file_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE folder ADD CONSTRAINT FK_ECA209CD727ACA70 FOREIGN KEY (parent_id) REFERENCES folder (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file ADD folder_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file ADD CONSTRAINT FK_8C9F3610162CB942 FOREIGN KEY (folder_id) REFERENCES folder (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8C9F3610162CB942 ON file (folder_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE file DROP CONSTRAINT FK_8C9F3610162CB942
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file_tag_mapping DROP CONSTRAINT FK_596CFF4293CB796C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file_tag_mapping DROP CONSTRAINT FK_596CFF422C545AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE folder DROP CONSTRAINT FK_ECA209CD727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE file_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE file_tag_mapping
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE folder
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8C9F3610162CB942
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file DROP folder_id
        SQL);
    }
}

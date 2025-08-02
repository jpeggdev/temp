<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250629234509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create filesystem_node structure and migrate existing files';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE filesystem_node (
                id SERIAL NOT NULL,
                parent_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                uuid UUID NOT NULL,
                type VARCHAR(255) NOT NULL,
                original_filename VARCHAR(255) DEFAULT NULL,
                bucket_name VARCHAR(255) DEFAULT NULL,
                object_key TEXT DEFAULT NULL,
                content_type VARCHAR(255) DEFAULT NULL,
                mime_type VARCHAR(100) DEFAULT NULL,
                file_size INT DEFAULT NULL,
                url VARCHAR(255) DEFAULT NULL,
                path VARCHAR(1024) DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql('CREATE INDEX IDX_4E10707D727ACA70 ON filesystem_node (parent_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E10707DD17F50A6 ON filesystem_node (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E10707D727ACA705E237E06 ON filesystem_node (parent_id, name)');

        $this->addSql(<<<'SQL'
            INSERT INTO filesystem_node (
                id, parent_id, name, created_at, updated_at, uuid, type,
                original_filename, bucket_name, object_key, content_type,
                mime_type, file_size, url
            )
            SELECT
                id, folder_id, original_filename, created_at, updated_at, uuid, 'file',
                original_filename, bucket_name, object_key, content_type,
                mime_type, file_size, url
            FROM file
        SQL);

        $this->addSql("SELECT setval('filesystem_node_id_seq', (SELECT COALESCE(MAX(id), 1) FROM filesystem_node))");

        $this->addSql('ALTER TABLE filesystem_node ADD CONSTRAINT FK_4E10707D727ACA70 FOREIGN KEY (parent_id) REFERENCES filesystem_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE resource DROP CONSTRAINT fk_bc91f416fdff2e92');
        $this->addSql('ALTER TABLE file_tag_mapping DROP CONSTRAINT fk_596cff4293cb796c');
        $this->addSql('ALTER TABLE event_files DROP CONSTRAINT fk_472ef17593cb796c');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT fk_3bae0aa7fdff2e92');
        $this->addSql('ALTER TABLE resource_content_block DROP CONSTRAINT fk_2134e53693cb796c');
        $this->addSql('ALTER TABLE file_tmp DROP CONSTRAINT fk_3acf3d193cb796c');

        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7FDFF2E92 FOREIGN KEY (thumbnail_id) REFERENCES filesystem_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_files ADD CONSTRAINT FK_472EF17593CB796C FOREIGN KEY (file_id) REFERENCES filesystem_node (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE file_tag_mapping ADD CONSTRAINT FK_596CFF4293CB796C FOREIGN KEY (file_id) REFERENCES filesystem_node (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE file_tmp ADD CONSTRAINT FK_3ACF3D193CB796C FOREIGN KEY (file_id) REFERENCES filesystem_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F416FDFF2E92 FOREIGN KEY (thumbnail_id) REFERENCES filesystem_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resource_content_block ADD CONSTRAINT FK_2134E53693CB796C FOREIGN KEY (file_id) REFERENCES filesystem_node (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('DROP SEQUENCE file_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE folder_id_seq CASCADE');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE folder');
    }

    public function down(Schema $schema): void
    {
        // noop
    }
}

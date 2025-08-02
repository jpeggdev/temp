<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add search vector functionality to filesystem_node table
 */
final class Version20250702181141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add search vector functionality to filesystem_node table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE filesystem_node ADD COLUMN IF NOT EXISTS search_vector tsvector');

        $this->addSql('CREATE INDEX IF NOT EXISTS idx_filesystem_node_search_vector ON filesystem_node USING gin(search_vector)');

        $this->dropAllTriggers();

        $this->createSearchVectorFunction();

        $this->createBumpNodeFunction();

        $this->createTagMappingTriggers();

        $this->createTagFunction();

        $this->addSql('UPDATE filesystem_node SET search_vector = NULL');
    }

    private function dropAllTriggers(): void
    {
        $this->addSql('DROP TRIGGER IF EXISTS app_filesystem_node_search_vector_trigger ON filesystem_node');
        $this->addSql('DROP TRIGGER IF EXISTS app_fsnm_after_insert ON file_system_node_tag_mapping');
        $this->addSql('DROP TRIGGER IF EXISTS app_fsnm_after_update ON file_system_node_tag_mapping');
        $this->addSql('DROP TRIGGER IF EXISTS app_fsnm_after_delete ON file_system_node_tag_mapping');
        $this->addSql('DROP TRIGGER IF EXISTS app_fsn_tag_after_update ON file_system_node_tag');
        $this->addSql('DROP TRIGGER IF EXISTS app_fsn_tag_after_delete ON file_system_node_tag');
        $this->addSql('DROP FUNCTION IF EXISTS app_filesystem_node_search_vector_before() CASCADE');
        $this->addSql('DROP FUNCTION IF EXISTS app_bump_filesystem_node_updated_at() CASCADE');
        $this->addSql('DROP FUNCTION IF EXISTS app_bump_nodes_for_tag() CASCADE');
    }

    private function createSearchVectorFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_filesystem_node_search_vector_before()
RETURNS TRIGGER AS $$
DECLARE
    tags_text text := '';
BEGIN
    IF NEW.id IS NOT NULL THEN
        SELECT COALESCE(string_agg(fsnt.name,' '),'') INTO tags_text
        FROM file_system_node_tag_mapping fsnm
        JOIN file_system_node_tag fsnt ON fsnt.id = fsnm.file_system_node_tag_id
        WHERE fsnm.file_system_node_id = NEW.id;
    END IF;

    NEW.search_vector := to_tsvector(
        'english',
        COALESCE(NEW.name,'') || ' ' ||
        COALESCE(NEW.uuid::text,'') || ' ' ||
        CASE WHEN NEW.type = 'file' THEN COALESCE(NEW.mime_type,'') ELSE '' END || ' ' ||
        COALESCE(tags_text,'')
    );

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
SQL);

        $this->addSql('
            CREATE TRIGGER app_filesystem_node_search_vector_trigger
            BEFORE INSERT OR UPDATE ON filesystem_node
            FOR EACH ROW EXECUTE FUNCTION app_filesystem_node_search_vector_before();
        ');
    }

    private function createBumpNodeFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_bump_filesystem_node_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    IF (pg_trigger_depth() > 15) THEN
        RETURN COALESCE(NEW, OLD);
    END IF;

    IF (TG_OP = 'DELETE') THEN
        UPDATE filesystem_node
        SET updated_at = NOW()
        WHERE id = OLD.file_system_node_id;
        RETURN OLD;
    ELSE
        UPDATE filesystem_node
        SET
            updated_at = NOW(),
            search_vector = NULL
        WHERE id = NEW.file_system_node_id;
        RETURN NEW;
    END IF;
END;
$$ LANGUAGE plpgsql;
SQL);
    }

    private function createTagMappingTriggers(): void
    {
        $this->addSql('CREATE TRIGGER app_fsnm_after_insert AFTER INSERT ON file_system_node_tag_mapping FOR EACH ROW EXECUTE FUNCTION app_bump_filesystem_node_updated_at();');
        $this->addSql('CREATE TRIGGER app_fsnm_after_update AFTER UPDATE ON file_system_node_tag_mapping FOR EACH ROW EXECUTE FUNCTION app_bump_filesystem_node_updated_at();');
        $this->addSql('CREATE TRIGGER app_fsnm_after_delete AFTER DELETE ON file_system_node_tag_mapping FOR EACH ROW EXECUTE FUNCTION app_bump_filesystem_node_updated_at();');
    }

    private function createTagFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_bump_nodes_for_tag()
RETURNS TRIGGER AS $$
BEGIN
    IF (pg_trigger_depth() > 15) THEN
        RETURN NULL;
    END IF;

    UPDATE filesystem_node
    SET
        updated_at = NOW(),
        search_vector = NULL
    WHERE id IN (
        SELECT file_system_node_id
        FROM file_system_node_tag_mapping
        WHERE file_system_node_tag_id = COALESCE(NEW.id, OLD.id)
    );
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
SQL);

        $this->addSql('CREATE TRIGGER app_fsn_tag_after_update AFTER UPDATE ON file_system_node_tag FOR EACH ROW EXECUTE FUNCTION app_bump_nodes_for_tag();');
        $this->addSql('CREATE TRIGGER app_fsn_tag_after_delete AFTER DELETE ON file_system_node_tag FOR EACH ROW EXECUTE FUNCTION app_bump_nodes_for_tag();');
    }

    public function down(Schema $schema): void
    {
        $this->dropAllTriggers();
        $this->addSql('DROP INDEX IF EXISTS idx_filesystem_node_search_vector');
    }
}

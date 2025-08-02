<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Remove debug logging from folder size calculation triggers
 */
final class Version20250713150854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove debug logging from folder size calculation triggers';
    }

    public function up(Schema $schema): void
    {
        $this->dropTriggers();
        $this->createCleanFolderSizeFunction();
        $this->createCleanTriggerFunction();
        $this->createTriggerOnTable();
        $this->createCleanInitializationFunction();
        $this->dropDebugTable();
    }

    private function dropTriggers(): void
    {
        $this->addSql('DROP TRIGGER IF EXISTS app_filesystem_node_size_trigger ON filesystem_node');
        $this->addSql('DROP FUNCTION IF EXISTS app_update_parent_folder_size() CASCADE');
        $this->addSql('DROP FUNCTION IF EXISTS update_folder_size(INTEGER) CASCADE');
        $this->addSql('DROP FUNCTION IF EXISTS initialize_all_folder_sizes() CASCADE');
    }

    private function createCleanFolderSizeFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION update_folder_size(folder_id INTEGER)
RETURNS VOID AS $$
DECLARE
    current_parent_id INTEGER;
    calculated_size BIGINT;
    folder_type VARCHAR;
BEGIN
    -- Get folder info
    SELECT parent_id, type INTO current_parent_id, folder_type
    FROM filesystem_node
    WHERE id = folder_id;

    -- Only proceed if this is actually a folder
    IF folder_type != 'folder' THEN
        RETURN;
    END IF;

    -- Calculate the new size for the folder
    SELECT COALESCE(SUM(child.file_size), 0) INTO calculated_size
    FROM filesystem_node child
    WHERE child.parent_id = folder_id;

    -- Update the folder size
    UPDATE filesystem_node
    SET file_size = calculated_size
    WHERE id = folder_id;

    -- Recursively update parent folders
    IF current_parent_id IS NOT NULL THEN
        PERFORM update_folder_size(current_parent_id);
    END IF;
END;
$$ LANGUAGE plpgsql;
SQL);
    }

    private function createCleanTriggerFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_update_parent_folder_size()
RETURNS TRIGGER AS $$
BEGIN
    IF (TG_OP = 'DELETE') THEN
        IF OLD.parent_id IS NOT NULL THEN
            PERFORM update_folder_size(OLD.parent_id);
        END IF;
        RETURN OLD;

    ELSIF (TG_OP = 'UPDATE') THEN
        -- Check if relevant fields changed
        IF (OLD.parent_id IS DISTINCT FROM NEW.parent_id) OR (OLD.file_size IS DISTINCT FROM NEW.file_size) THEN
            IF OLD.parent_id IS NOT NULL THEN
                PERFORM update_folder_size(OLD.parent_id);
            END IF;

            IF NEW.parent_id IS NOT NULL AND (OLD.parent_id IS DISTINCT FROM NEW.parent_id) THEN
                PERFORM update_folder_size(NEW.parent_id);
            END IF;
        END IF;
        RETURN NEW;

    ELSIF (TG_OP = 'INSERT') THEN
        IF NEW.parent_id IS NOT NULL THEN
            PERFORM update_folder_size(NEW.parent_id);
        END IF;
        RETURN NEW;
    END IF;

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
SQL);
    }

    private function createTriggerOnTable(): void
    {
        $this->addSql(<<<'SQL'
CREATE CONSTRAINT TRIGGER app_filesystem_node_size_trigger
AFTER INSERT OR UPDATE OR DELETE ON filesystem_node
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW EXECUTE FUNCTION app_update_parent_folder_size();
SQL);
    }

    private function createCleanInitializationFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION initialize_all_folder_sizes()
RETURNS VOID AS $$
DECLARE
    updates_made BOOLEAN := true;
    rows_affected INTEGER;
    max_iterations INTEGER := 100;
    iteration_count INTEGER := 0;
BEGIN
    -- Ensure all files have a non-null file_size
    UPDATE filesystem_node
    SET file_size = 0
    WHERE type = 'file' AND file_size IS NULL;

    -- First initialize leaf folders (those without subfolders)
    UPDATE filesystem_node f
    SET file_size = COALESCE((
        SELECT SUM(child.file_size)
        FROM filesystem_node child
        WHERE child.parent_id = f.id
          AND child.type = 'file'
    ), 0)
    WHERE f.type = 'folder'
    AND NOT EXISTS (
        SELECT 1 FROM filesystem_node child
        WHERE child.parent_id = f.id
        AND child.type = 'folder'
    );

    -- Then work up the hierarchy, level by level
    WHILE updates_made AND iteration_count < max_iterations LOOP
        iteration_count := iteration_count + 1;

        WITH folders_to_update AS (
            SELECT f.id
            FROM filesystem_node f
            WHERE f.type = 'folder'
            AND (
                f.file_size IS NULL
                OR f.file_size <> COALESCE((
                    SELECT SUM(child.file_size)
                    FROM filesystem_node child
                    WHERE child.parent_id = f.id
                ), 0)
            )
            AND NOT EXISTS (
                SELECT 1 FROM filesystem_node child
                WHERE child.parent_id = f.id
                AND child.type = 'folder'
                AND (
                    child.file_size IS NULL
                    OR child.file_size <> COALESCE((
                        SELECT SUM(grandchild.file_size)
                        FROM filesystem_node grandchild
                        WHERE grandchild.parent_id = child.id
                    ), 0)
                )
            )
            LIMIT 1000
        )
        UPDATE filesystem_node f
        SET file_size = COALESCE((
            SELECT SUM(child.file_size)
            FROM filesystem_node child
            WHERE child.parent_id = f.id
        ), 0)
        FROM folders_to_update
        WHERE f.id = folders_to_update.id;

        GET DIAGNOSTICS rows_affected = ROW_COUNT;
        updates_made := rows_affected > 0;
    END LOOP;
END;
$$ LANGUAGE plpgsql;
SQL);

        // Run the function to initialize all folder sizes
        $this->addSql('SELECT initialize_all_folder_sizes()');
    }

    private function dropDebugTable(): void
    {
        $this->addSql('DROP TABLE IF EXISTS trigger_debug_log');
    }

    public function down(Schema $schema): void
    {
    }
}

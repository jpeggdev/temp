<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Debug folder size calculation triggers
 */
final class Version20250712005817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add debugging to folder size calculation triggers';
    }

    public function up(Schema $schema): void
    {
        $this->createDebugTable();
        $this->dropTriggers();
        $this->createFolderSizeFunction();
        $this->createTriggerFunction();
        $this->createTriggerOnTable();
        $this->createInitializationFunction();
        $this->addSql('SELECT initialize_all_folder_sizes()');
    }

    private function createDebugTable(): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE IF NOT EXISTS trigger_debug_log (
    id SERIAL PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    operation VARCHAR(50),
    node_id INTEGER,
    parent_id INTEGER,
    file_size BIGINT,
    message TEXT
);
SQL);
    }

    private function dropTriggers(): void
    {
        $this->addSql('DROP TRIGGER IF EXISTS app_filesystem_node_size_trigger ON filesystem_node');
        $this->addSql('DROP FUNCTION IF EXISTS app_update_parent_folder_size() CASCADE');
        $this->addSql('DROP FUNCTION IF EXISTS update_folder_size(INTEGER) CASCADE');
        $this->addSql('DROP FUNCTION IF EXISTS initialize_all_folder_sizes() CASCADE');
    }

    private function createFolderSizeFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION update_folder_size(folder_id INTEGER)
RETURNS VOID AS $$
DECLARE
    current_parent_id INTEGER;
    calculated_size BIGINT;
    folder_type VARCHAR;
BEGIN
    -- Log the start of the function call
    INSERT INTO trigger_debug_log (operation, node_id, message)
    VALUES ('UPDATE_FOLDER_SIZE_START', folder_id, 'Starting folder size update');

    -- Get folder info
    SELECT parent_id, type INTO current_parent_id, folder_type
    FROM filesystem_node
    WHERE id = folder_id;

    -- Log the folder type
    INSERT INTO trigger_debug_log (operation, node_id, parent_id, message)
    VALUES ('FOLDER_INFO', folder_id, current_parent_id, 'Folder type: ' || folder_type);

    -- Only proceed if this is actually a folder
    IF folder_type != 'folder' THEN
        INSERT INTO trigger_debug_log (operation, node_id, message)
        VALUES ('ERROR', folder_id, 'Tried to update size of non-folder: ' || folder_type);
        RETURN;
    END IF;

    -- Calculate the new size for the folder
    SELECT COALESCE(SUM(child.file_size), 0) INTO calculated_size
    FROM filesystem_node child
    WHERE child.parent_id = folder_id;

    -- Log the calculated size
    INSERT INTO trigger_debug_log (operation, node_id, file_size, message)
    VALUES ('SIZE_CALCULATION', folder_id, calculated_size, 'Calculated folder size');

    -- Count how many children this folder has
    INSERT INTO trigger_debug_log (operation, node_id, message)
    VALUES ('CHILD_COUNT', folder_id, 'Child count: ' || (
        SELECT COUNT(*) FROM filesystem_node WHERE parent_id = folder_id
    ));

    -- Update the folder size
    UPDATE filesystem_node
    SET file_size = calculated_size
    WHERE id = folder_id;

    -- Log the update
    INSERT INTO trigger_debug_log (operation, node_id, file_size, message)
    VALUES ('UPDATE_COMPLETE', folder_id, calculated_size, 'Updated folder size');

    -- Recursively update parent folders
    IF current_parent_id IS NOT NULL THEN
        INSERT INTO trigger_debug_log (operation, node_id, parent_id, message)
        VALUES ('RECURSIVE_CALL', folder_id, current_parent_id, 'Recursively updating parent');

        PERFORM update_folder_size(current_parent_id);
    END IF;

    -- Log completion
    INSERT INTO trigger_debug_log (operation, node_id, message)
    VALUES ('UPDATE_FOLDER_SIZE_END', folder_id, 'Completed folder size update');
END;
$$ LANGUAGE plpgsql;
SQL);
    }

    private function createTriggerFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_update_parent_folder_size()
RETURNS TRIGGER AS $$
BEGIN
    -- Log trigger execution
    IF (TG_OP = 'DELETE') THEN
        INSERT INTO trigger_debug_log (operation, node_id, parent_id, file_size, message)
        VALUES ('TRIGGER_DELETE', OLD.id, OLD.parent_id, OLD.file_size, 'Delete operation triggered');

        IF OLD.parent_id IS NOT NULL THEN
            PERFORM update_folder_size(OLD.parent_id);
        END IF;
        RETURN OLD;

    ELSIF (TG_OP = 'UPDATE') THEN
        INSERT INTO trigger_debug_log (operation, node_id, parent_id, file_size, message)
        VALUES ('TRIGGER_UPDATE', NEW.id, NEW.parent_id, NEW.file_size,
                'Update operation triggered. Old parent: ' || OLD.parent_id ||
                ', New parent: ' || NEW.parent_id ||
                ', Old size: ' || OLD.file_size ||
                ', New size: ' || NEW.file_size);

        -- Check if relevant fields changed
        IF (OLD.parent_id IS DISTINCT FROM NEW.parent_id) OR (OLD.file_size IS DISTINCT FROM NEW.file_size) THEN
            INSERT INTO trigger_debug_log (operation, node_id, message)
            VALUES ('UPDATE_RELEVANT', NEW.id, 'Relevant fields changed');

            IF OLD.parent_id IS NOT NULL THEN
                PERFORM update_folder_size(OLD.parent_id);
            END IF;

            IF NEW.parent_id IS NOT NULL AND (OLD.parent_id IS DISTINCT FROM NEW.parent_id) THEN
                PERFORM update_folder_size(NEW.parent_id);
            END IF;
        ELSE
            INSERT INTO trigger_debug_log (operation, node_id, message)
            VALUES ('UPDATE_SKIPPED', NEW.id, 'No relevant changes, skipping update');
        END IF;
        RETURN NEW;

    ELSIF (TG_OP = 'INSERT') THEN
        INSERT INTO trigger_debug_log (operation, node_id, parent_id, file_size, message)
        VALUES ('TRIGGER_INSERT', NEW.id, NEW.parent_id, NEW.file_size,
                'Insert operation triggered. Type: ' || NEW.type ||
                ', Parent ID: ' || NEW.parent_id);

        IF NEW.parent_id IS NOT NULL THEN
            INSERT INTO trigger_debug_log (operation, node_id, parent_id, message)
            VALUES ('UPDATING_PARENT', NEW.id, NEW.parent_id, 'Updating parent folder');

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

    private function createInitializationFunction(): void
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
    -- Log start of initialization
    INSERT INTO trigger_debug_log (operation, message)
    VALUES ('INIT_START', 'Starting folder size initialization');

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

        INSERT INTO trigger_debug_log (operation, message)
        VALUES ('INIT_ITERATION', 'Iteration ' || iteration_count);

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

        INSERT INTO trigger_debug_log (operation, message)
        VALUES ('INIT_UPDATED', 'Updated ' || rows_affected || ' folders');

        updates_made := rows_affected > 0;
    END LOOP;

    -- Log completion
    INSERT INTO trigger_debug_log (operation, message)
    VALUES ('INIT_COMPLETE', 'Completed folder size initialization');
END;
$$ LANGUAGE plpgsql;
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->dropTriggers();
        $this->addSql('DROP TABLE IF EXISTS trigger_debug_log');
        $this->addSql("UPDATE filesystem_node SET file_size = NULL WHERE type = 'folder'");
    }
}

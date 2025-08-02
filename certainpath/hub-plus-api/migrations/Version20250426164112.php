<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Enhances resource search vector functionality with recursion protection and reference table triggers.
 */
final class Version20250426164112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enhances resource search vector functionality with recursion protection and reference table triggers';
    }

    public function up(Schema $schema): void
    {
        $this->dropAllTriggers();
        $this->updateSearchVectorFunction();
        $this->createBumpResourceFunction();
        $this->createAllMappingTriggers();
        $this->addReferenceFunctions();
    }

    // region dropAllTriggers
    private function dropAllTriggers(): void
    {
        $this->addSql('DROP TRIGGER IF EXISTS app_resource_tag_after_update ON resource_tag;');
        $this->addSql('DROP TRIGGER IF EXISTS app_resource_tag_after_delete ON resource_tag;');
        $this->addSql('DROP TRIGGER IF EXISTS app_resource_category_after_update ON resource_category;');
        $this->addSql('DROP TRIGGER IF EXISTS app_resource_category_after_delete ON resource_category;');
        $this->addSql('DROP TRIGGER IF EXISTS app_resource_trade_after_update ON trade;');
        $this->addSql('DROP TRIGGER IF EXISTS app_resource_trade_after_delete ON trade;');
        $this->addSql('DROP TRIGGER IF EXISTS app_resource_role_after_update ON employee_role;');
        $this->addSql('DROP TRIGGER IF EXISTS app_resource_role_after_delete ON employee_role;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rtm_after_insert ON resource_tag_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rtm_after_update ON resource_tag_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rtm_after_delete ON resource_tag_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rcm_after_insert ON resource_category_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rcm_after_update ON resource_category_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rcm_after_delete ON resource_category_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rtr_after_insert ON resource_trade_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rtr_after_update ON resource_trade_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rtr_after_delete ON resource_trade_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rerm_after_insert ON resource_employee_role_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rerm_after_update ON resource_employee_role_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rerm_after_delete ON resource_employee_role_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rcb_after_insert ON resource_content_block;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rcb_after_update ON resource_content_block;');
        $this->addSql('DROP TRIGGER IF EXISTS app_rcb_after_delete ON resource_content_block;');
        $this->addSql('DROP TRIGGER IF EXISTS app_resource_search_vector_trigger ON resource;');
    }
    // endregion

    // region updateSearchVectorFunction
    private function updateSearchVectorFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_resource_search_vector_before()
RETURNS TRIGGER AS $$
DECLARE
    cat_text   text := '';
    tag_text   text := '';
    trade_text text := '';
    role_text  text := '';
    cb_text    text := '';
BEGIN
    IF NEW.id IS NOT NULL THEN
        SELECT COALESCE(string_agg(rc.name, ' '), '')
          INTO cat_text
          FROM resource_category_mapping rcm
          JOIN resource_category rc ON rcm.resource_category_id = rc.id
         WHERE rcm.resource_id = NEW.id;

        SELECT COALESCE(string_agg(rt.name, ' '), '')
          INTO tag_text
          FROM resource_tag_mapping rtm
          JOIN resource_tag rt ON rtm.resource_tag_id = rt.id
         WHERE rtm.resource_id = NEW.id;

        SELECT COALESCE(string_agg(tr.name, ' '), '')
          INTO trade_text
          FROM resource_trade_mapping rtr
          JOIN trade tr ON rtr.trade_id = tr.id
         WHERE rtr.resource_id = NEW.id;

        SELECT COALESCE(string_agg(er.name, ' '), '')
          INTO role_text
          FROM resource_employee_role_mapping rerm
          JOIN employee_role er ON rerm.employee_role_id = er.id
         WHERE rerm.resource_id = NEW.id;

        SELECT COALESCE(string_agg(rcb.content, ' '), '')
          INTO cb_text
          FROM resource_content_block rcb
         WHERE rcb.resource_id = NEW.id;
    END IF;

    NEW.search_vector := to_tsvector(
      'english',
      COALESCE(NEW.title, '') || ' ' ||
      COALESCE(NEW.description, '') || ' ' ||
      COALESCE(NEW.tagline, '') || ' ' ||
      COALESCE(cat_text, '') || ' ' ||
      COALESCE(tag_text, '') || ' ' ||
      COALESCE(trade_text, '') || ' ' ||
      COALESCE(role_text, '') || ' ' ||
      COALESCE(cb_text, '')
    );

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
SQL);

        $this->addSql('
            CREATE TRIGGER app_resource_search_vector_trigger
            BEFORE INSERT OR UPDATE ON resource
            FOR EACH ROW
            EXECUTE FUNCTION app_resource_search_vector_before();
        ');
    }
    // endregion

    // region createBumpResourceFunction
    private function createBumpResourceFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_bump_resource_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    IF (pg_trigger_depth() > 15) THEN
        RETURN COALESCE(NEW, OLD);
    END IF;

    IF (TG_OP = 'DELETE') THEN
        UPDATE resource 
        SET updated_at = NOW()
        WHERE id = OLD.resource_id;
        RETURN OLD;
    ELSE
        UPDATE resource
        SET 
            updated_at = NOW(),
            search_vector = NULL  
        WHERE id = NEW.resource_id;
        RETURN NEW;
    END IF;
END;
$$ LANGUAGE plpgsql;
SQL);
    }
    // endregion

    // region createAllMappingTriggers
    private function createAllMappingTriggers(): void
    {
        $this->createTagMappingTriggers();
        $this->createCategoryMappingTriggers();
        $this->createTradeMappingTriggers();
        $this->createRoleMappingTriggers();
        $this->createContentBlockTriggers();
    }
    // endregion

    // region createTagMappingTriggers
    private function createTagMappingTriggers(): void
    {
        $this->addSql('
            CREATE TRIGGER app_rtm_after_insert
            AFTER INSERT ON resource_tag_mapping
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
        $this->addSql('
            CREATE TRIGGER app_rtm_after_update
            AFTER UPDATE ON resource_tag_mapping
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
        $this->addSql('
            CREATE TRIGGER app_rtm_after_delete
            AFTER DELETE ON resource_tag_mapping
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
    }
    // endregion

    // region createCategoryMappingTriggers
    private function createCategoryMappingTriggers(): void
    {
        $this->addSql('
            CREATE TRIGGER app_rcm_after_insert
            AFTER INSERT ON resource_category_mapping
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
        $this->addSql('
            CREATE TRIGGER app_rcm_after_update
            AFTER UPDATE ON resource_category_mapping
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
        $this->addSql('
            CREATE TRIGGER app_rcm_after_delete
            AFTER DELETE ON resource_category_mapping
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
    }
    // endregion

    // region createTradeMappingTriggers
    private function createTradeMappingTriggers(): void
    {
        $this->addSql('
            CREATE TRIGGER app_rtr_after_insert
            AFTER INSERT ON resource_trade_mapping
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
        $this->addSql('
            CREATE TRIGGER app_rtr_after_update
            AFTER UPDATE ON resource_trade_mapping
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
        $this->addSql('
            CREATE TRIGGER app_rtr_after_delete
            AFTER DELETE ON resource_trade_mapping
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
    }
    // endregion

    // region createRoleMappingTriggers
    private function createRoleMappingTriggers(): void
    {
        $this->addSql('
            CREATE TRIGGER app_rerm_after_insert
            AFTER INSERT ON resource_employee_role_mapping
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
        $this->addSql('
            CREATE TRIGGER app_rerm_after_update
            AFTER UPDATE ON resource_employee_role_mapping
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
        $this->addSql('
            CREATE TRIGGER app_rerm_after_delete
            AFTER DELETE ON resource_employee_role_mapping
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
    }
    // endregion

    // region createContentBlockTriggers
    private function createContentBlockTriggers(): void
    {
        $this->addSql('
            CREATE TRIGGER app_rcb_after_insert
            AFTER INSERT ON resource_content_block
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
        $this->addSql('
            CREATE TRIGGER app_rcb_after_update
            AFTER UPDATE ON resource_content_block
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
        $this->addSql('
            CREATE TRIGGER app_rcb_after_delete
            AFTER DELETE ON resource_content_block
            FOR EACH ROW
            EXECUTE FUNCTION app_bump_resource_updated_at();
        ');
    }
    // endregion

    // region addReferenceFunctions
    private function addReferenceFunctions(): void
    {
        $this->createTagReferenceFunction();
        $this->createCategoryReferenceFunction();
        $this->createTradeReferenceFunction();
        $this->createRoleReferenceFunction();
    }
    // endregion

    // region createTagReferenceFunction
    private function createTagReferenceFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_bump_resources_for_tag()
RETURNS TRIGGER AS $$
BEGIN
    IF (pg_trigger_depth() > 15) THEN
        RETURN NULL;
    END IF;

    UPDATE resource 
    SET 
        updated_at = NOW(),
        search_vector = NULL  
    WHERE id IN (
        SELECT resource_id 
        FROM resource_tag_mapping
        WHERE resource_tag_id = COALESCE(NEW.id, OLD.id)
    );
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
SQL);

        $this->addSql('CREATE TRIGGER app_resource_tag_after_update AFTER UPDATE ON resource_tag FOR EACH ROW EXECUTE FUNCTION app_bump_resources_for_tag();');
        $this->addSql('CREATE TRIGGER app_resource_tag_after_delete AFTER DELETE ON resource_tag FOR EACH ROW EXECUTE FUNCTION app_bump_resources_for_tag();');
    }
    // endregion

    // region createCategoryReferenceFunction
    private function createCategoryReferenceFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_bump_resources_for_category()
RETURNS TRIGGER AS $$
BEGIN
    IF (pg_trigger_depth() > 15) THEN
        RETURN NULL;
    END IF;

    UPDATE resource 
    SET 
        updated_at = NOW(),
        search_vector = NULL  
    WHERE id IN (
        SELECT resource_id 
        FROM resource_category_mapping
        WHERE resource_category_id = COALESCE(NEW.id, OLD.id)
    );
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
SQL);

        $this->addSql('CREATE TRIGGER app_resource_category_after_update AFTER UPDATE ON resource_category FOR EACH ROW EXECUTE FUNCTION app_bump_resources_for_category();');
        $this->addSql('CREATE TRIGGER app_resource_category_after_delete AFTER DELETE ON resource_category FOR EACH ROW EXECUTE FUNCTION app_bump_resources_for_category();');
    }
    // endregion

    // region createTradeReferenceFunction
    private function createTradeReferenceFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_bump_resources_for_trade()
RETURNS TRIGGER AS $$
BEGIN
    IF (pg_trigger_depth() > 15) THEN
        RETURN NULL;
    END IF;

    UPDATE resource 
    SET 
        updated_at = NOW(),
        search_vector = NULL
    WHERE id IN (
        SELECT resource_id 
        FROM resource_trade_mapping
        WHERE trade_id = COALESCE(NEW.id, OLD.id)
    );
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
SQL);

        $this->addSql('CREATE TRIGGER app_resource_trade_after_update AFTER UPDATE ON trade FOR EACH ROW EXECUTE FUNCTION app_bump_resources_for_trade();');
        $this->addSql('CREATE TRIGGER app_resource_trade_after_delete AFTER DELETE ON trade FOR EACH ROW EXECUTE FUNCTION app_bump_resources_for_trade();');
    }
    // endregion

    // region createRoleReferenceFunction
    private function createRoleReferenceFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_bump_resources_for_role()
RETURNS TRIGGER AS $$
BEGIN
    IF (pg_trigger_depth() > 15) THEN
        RETURN NULL;
    END IF;

    UPDATE resource 
    SET 
        updated_at = NOW(),
        search_vector = NULL  
    WHERE id IN (
        SELECT resource_id 
        FROM resource_employee_role_mapping
        WHERE employee_role_id = COALESCE(NEW.id, OLD.id)
    );
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
SQL);

        $this->addSql('CREATE TRIGGER app_resource_role_after_update AFTER UPDATE ON employee_role FOR EACH ROW EXECUTE FUNCTION app_bump_resources_for_role();');
        $this->addSql('CREATE TRIGGER app_resource_role_after_delete AFTER DELETE ON employee_role FOR EACH ROW EXECUTE FUNCTION app_bump_resources_for_role();');
    }
    // endregion

    // region down
    public function down(Schema $schema): void
    {
        $this->dropAllTriggers();
        $this->addSql('DROP FUNCTION IF EXISTS app_bump_resources_for_tag();');
        $this->addSql('DROP FUNCTION IF EXISTS app_bump_resources_for_category();');
        $this->addSql('DROP FUNCTION IF EXISTS app_bump_resources_for_trade();');
        $this->addSql('DROP FUNCTION IF EXISTS app_bump_resources_for_role();');
        $this->addSql('DROP FUNCTION IF EXISTS app_bump_resource_updated_at();');
        $this->addSql('DROP FUNCTION IF EXISTS app_resource_search_vector_before();');
    }
    // endregion
}

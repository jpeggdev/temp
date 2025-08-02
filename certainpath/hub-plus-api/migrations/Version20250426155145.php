<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix event search vector to update with any relation change.
 */
final class Version20250426155145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix event search vector to update with any relation change';
    }

    public function up(Schema $schema): void
    {
        $this->dropAllTriggers();
        $this->updateSearchVectorFunction();
        $this->createBumpEventFunction();
        $this->createAllMappingTriggers();
        $this->createReferenceFunctions();
    }

    // region dropAllTriggers
    private function dropAllTriggers(): void
    {
        $this->addSql('DROP TRIGGER IF EXISTS app_event_search_vector_trigger ON event;');
        $this->addSql('DROP TRIGGER IF EXISTS app_etm_after_insert  ON event_tag_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_etm_after_update  ON event_tag_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_etm_after_delete  ON event_tag_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_etrm_after_insert ON event_trade_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_etrm_after_update ON event_trade_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_etrm_after_delete ON event_trade_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_erm_after_insert  ON event_employee_role_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_erm_after_update  ON event_employee_role_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_erm_after_delete  ON event_employee_role_mapping;');
        $this->addSql('DROP TRIGGER IF EXISTS app_ec_after_update ON event_category;');
        $this->addSql('DROP TRIGGER IF EXISTS app_ec_after_delete ON event_category;');
        $this->addSql('DROP TRIGGER IF EXISTS app_et_after_update ON event_type;');
        $this->addSql('DROP TRIGGER IF EXISTS app_et_after_delete ON event_type;');
        $this->addSql('DROP TRIGGER IF EXISTS app_tag_after_update ON event_tag;');
        $this->addSql('DROP TRIGGER IF EXISTS app_tag_after_delete ON event_tag;');
        $this->addSql('DROP TRIGGER IF EXISTS app_trade_after_update ON trade;');
        $this->addSql('DROP TRIGGER IF EXISTS app_trade_after_delete ON trade;');
        $this->addSql('DROP TRIGGER IF EXISTS app_role_after_update ON employee_role;');
        $this->addSql('DROP TRIGGER IF EXISTS app_role_after_delete ON employee_role;');
    }
    // endregion

    // region updateSearchVectorFunction
    private function updateSearchVectorFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_event_search_vector_before()
RETURNS TRIGGER AS $$
DECLARE
    type_text  text := '';
    cat_text   text := '';
    tag_text   text := '';
    trade_text text := '';
    role_text  text := '';
BEGIN
    IF NEW.event_type_id IS NOT NULL THEN
        SELECT COALESCE(et.name,'') INTO type_text
        FROM event_type et WHERE et.id = NEW.event_type_id;
    END IF;

    IF NEW.event_category_id IS NOT NULL THEN
        SELECT COALESCE(ec.name,'') INTO cat_text
        FROM event_category ec WHERE ec.id = NEW.event_category_id;
    END IF;

    IF NEW.id IS NOT NULL THEN
        SELECT COALESCE(string_agg(etag.name,' '),'') INTO tag_text
        FROM event_tag_mapping etm
        JOIN event_tag etag ON etag.id = etm.event_tag_id
        WHERE etm.event_id = NEW.id;
    
        SELECT COALESCE(string_agg(t.name,' '),'') INTO trade_text
        FROM event_trade_mapping etm
        JOIN trade t ON t.id = etm.trade_id
        WHERE etm.event_id = NEW.id;
    
        SELECT COALESCE(string_agg(er.name,' '),'') INTO role_text
        FROM event_employee_role_mapping erm
        JOIN employee_role er ON er.id = erm.employee_role_id
        WHERE erm.event_id = NEW.id;
    END IF;

    NEW.search_vector := to_tsvector(
        'english',
        COALESCE(NEW.event_code,'')        || ' ' ||
        COALESCE(NEW.event_name,'')        || ' ' ||
        COALESCE(NEW.event_description,'') || ' ' ||
        COALESCE(NEW.event_price::text,'') || ' ' ||
        COALESCE(type_text,'')  || ' ' || 
        COALESCE(cat_text,'')   || ' ' ||
        COALESCE(tag_text,'')   || ' ' || 
        COALESCE(trade_text,'') || ' ' ||
        COALESCE(role_text,'')
    );
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
SQL);

        $this->addSql('
            CREATE TRIGGER app_event_search_vector_trigger
            BEFORE INSERT OR UPDATE ON event
            FOR EACH ROW EXECUTE FUNCTION app_event_search_vector_before();
        ');
    }
    // endregion

    // region createBumpEventFunction
    private function createBumpEventFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_bump_event_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    IF (pg_trigger_depth() > 15) THEN
        RETURN COALESCE(NEW, OLD);
    END IF;

    IF (TG_OP = 'DELETE') THEN
        UPDATE event SET updated_at = NOW()
         WHERE id = OLD.event_id;
        RETURN OLD;
    ELSE
        UPDATE event 
        SET 
            updated_at = NOW(), 
            search_vector = NULL 
        WHERE id = NEW.event_id;
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
        $this->createTradeMappingTriggers();
        $this->createRoleMappingTriggers();
    }
    // endregion

    // region createTagMappingTriggers
    private function createTagMappingTriggers(): void
    {
        $this->addSql('CREATE TRIGGER app_etm_after_insert AFTER INSERT ON event_tag_mapping FOR EACH ROW EXECUTE FUNCTION app_bump_event_updated_at();');
        $this->addSql('CREATE TRIGGER app_etm_after_update AFTER UPDATE ON event_tag_mapping FOR EACH ROW EXECUTE FUNCTION app_bump_event_updated_at();');
        $this->addSql('CREATE TRIGGER app_etm_after_delete AFTER DELETE ON event_tag_mapping FOR EACH ROW EXECUTE FUNCTION app_bump_event_updated_at();');
    }
    // endregion

    // region createTradeMappingTriggers
    private function createTradeMappingTriggers(): void
    {
        $this->addSql('CREATE TRIGGER app_etrm_after_insert AFTER INSERT ON event_trade_mapping FOR EACH ROW EXECUTE FUNCTION app_bump_event_updated_at();');
        $this->addSql('CREATE TRIGGER app_etrm_after_update AFTER UPDATE ON event_trade_mapping FOR EACH ROW EXECUTE FUNCTION app_bump_event_updated_at();');
        $this->addSql('CREATE TRIGGER app_etrm_after_delete AFTER DELETE ON event_trade_mapping FOR EACH ROW EXECUTE FUNCTION app_bump_event_updated_at();');
    }
    // endregion

    // region createRoleMappingTriggers
    private function createRoleMappingTriggers(): void
    {
        $this->addSql('CREATE TRIGGER app_erm_after_insert AFTER INSERT ON event_employee_role_mapping FOR EACH ROW EXECUTE FUNCTION app_bump_event_updated_at();');
        $this->addSql('CREATE TRIGGER app_erm_after_update AFTER UPDATE ON event_employee_role_mapping FOR EACH ROW EXECUTE FUNCTION app_bump_event_updated_at();');
        $this->addSql('CREATE TRIGGER app_erm_after_delete AFTER DELETE ON event_employee_role_mapping FOR EACH ROW EXECUTE FUNCTION app_bump_event_updated_at();');
    }
    // endregion

    // region createReferenceFunctions
    private function createReferenceFunctions(): void
    {
        $this->createCategoryTypeFunction();
        $this->createTagFunction();
        $this->createTradeFunction();
        $this->createRoleFunction();
    }
    // endregion

    // region createCategoryTypeFunction
    private function createCategoryTypeFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_bump_events_for_ref()
RETURNS TRIGGER AS $$
BEGIN
    IF (pg_trigger_depth() > 15) THEN
        RETURN NULL;
    END IF;

    UPDATE event 
    SET 
        updated_at = NOW(),
        search_vector = NULL
    WHERE event_category_id = COALESCE(NEW.id,OLD.id)
       OR event_type_id     = COALESCE(NEW.id,OLD.id);
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
SQL);

        $this->addSql('CREATE TRIGGER app_ec_after_update AFTER UPDATE ON event_category FOR EACH ROW EXECUTE FUNCTION app_bump_events_for_ref();');
        $this->addSql('CREATE TRIGGER app_ec_after_delete AFTER DELETE ON event_category FOR EACH ROW EXECUTE FUNCTION app_bump_events_for_ref();');
        $this->addSql('CREATE TRIGGER app_et_after_update AFTER UPDATE ON event_type FOR EACH ROW EXECUTE FUNCTION app_bump_events_for_ref();');
        $this->addSql('CREATE TRIGGER app_et_after_delete AFTER DELETE ON event_type FOR EACH ROW EXECUTE FUNCTION app_bump_events_for_ref();');
    }
    // endregion

    // region createTagFunction
    private function createTagFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_bump_events_for_tag()
RETURNS TRIGGER AS $$
BEGIN
    IF (pg_trigger_depth() > 15) THEN
        RETURN NULL;
    END IF;

    UPDATE event 
    SET 
        updated_at = NOW(),
        search_vector = NULL
    WHERE id IN (SELECT event_id FROM event_tag_mapping
                  WHERE event_tag_id = COALESCE(NEW.id,OLD.id));
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
SQL);

        $this->addSql('CREATE TRIGGER app_tag_after_update AFTER UPDATE ON event_tag FOR EACH ROW EXECUTE FUNCTION app_bump_events_for_tag();');
        $this->addSql('CREATE TRIGGER app_tag_after_delete AFTER DELETE ON event_tag FOR EACH ROW EXECUTE FUNCTION app_bump_events_for_tag();');
    }
    // endregion

    // region createTradeFunction
    private function createTradeFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_bump_events_for_trade()
RETURNS TRIGGER AS $$
BEGIN
    IF (pg_trigger_depth() > 15) THEN
        RETURN NULL;
    END IF;

    UPDATE event 
    SET 
        updated_at = NOW(),
        search_vector = NULL
    WHERE id IN (SELECT event_id FROM event_trade_mapping
                  WHERE trade_id = COALESCE(NEW.id,OLD.id));
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
SQL);

        $this->addSql('CREATE TRIGGER app_trade_after_update AFTER UPDATE ON trade FOR EACH ROW EXECUTE FUNCTION app_bump_events_for_trade();');
        $this->addSql('CREATE TRIGGER app_trade_after_delete AFTER DELETE ON trade FOR EACH ROW EXECUTE FUNCTION app_bump_events_for_trade();');
    }
    // endregion

    // region createRoleFunction
    private function createRoleFunction(): void
    {
        $this->addSql(<<<'SQL'
CREATE OR REPLACE FUNCTION app_bump_events_for_role()
RETURNS TRIGGER AS $$
BEGIN
    IF (pg_trigger_depth() > 15) THEN
        RETURN NULL;
    END IF;

    UPDATE event 
    SET 
        updated_at = NOW(),
        search_vector = NULL
    WHERE id IN (SELECT event_id FROM event_employee_role_mapping
                  WHERE employee_role_id = COALESCE(NEW.id,OLD.id));
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
SQL);

        $this->addSql('CREATE TRIGGER app_role_after_update AFTER UPDATE ON employee_role FOR EACH ROW EXECUTE FUNCTION app_bump_events_for_role();');
        $this->addSql('CREATE TRIGGER app_role_after_delete AFTER DELETE ON employee_role FOR EACH ROW EXECUTE FUNCTION app_bump_events_for_role();');
    }
    // endregion

    // region down
    public function down(Schema $schema): void
    {
        // Drop all triggers first
        $this->dropAllTriggers();

        // Drop all functions
        $this->addSql('DROP FUNCTION IF EXISTS app_bump_events_for_role();');
        $this->addSql('DROP FUNCTION IF EXISTS app_bump_events_for_trade();');
        $this->addSql('DROP FUNCTION IF EXISTS app_bump_events_for_tag();');
        $this->addSql('DROP FUNCTION IF EXISTS app_bump_events_for_ref();');
        $this->addSql('DROP FUNCTION IF EXISTS app_bump_event_updated_at();');
        $this->addSql('DROP FUNCTION IF EXISTS app_event_search_vector_before();');
    }
    // endregion
}

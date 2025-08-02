<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241011155543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // application_access_id_seq
        $this->addSql("
        DO $$ 
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'application_access_id_seq') THEN
                CREATE SEQUENCE application_access_id_seq;
            END IF;
        END
        $$;
    ");
        $this->addSql('SELECT setval(\'application_access_id_seq\', (SELECT COALESCE(MAX(id), 1) FROM application_access))');
        $this->addSql('ALTER TABLE application_access ALTER id SET DEFAULT nextval(\'application_access_id_seq\')');

        // audit_log_id_seq
        $this->addSql("
        DO $$ 
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'audit_log_id_seq') THEN
                CREATE SEQUENCE audit_log_id_seq;
            END IF;
        END
        $$;
    ");
        $this->addSql('SELECT setval(\'audit_log_id_seq\', (SELECT COALESCE(MAX(id), 1) FROM audit_log))');
        $this->addSql('ALTER TABLE audit_log ALTER id SET DEFAULT nextval(\'audit_log_id_seq\')');

        // business_role_id_seq
        $this->addSql("
        DO $$ 
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'business_role_id_seq') THEN
                CREATE SEQUENCE business_role_id_seq;
            END IF;
        END
        $$;
    ");
        $this->addSql('SELECT setval(\'business_role_id_seq\', (SELECT COALESCE(MAX(id), 1) FROM business_role))');
        $this->addSql('ALTER TABLE business_role ALTER id SET DEFAULT nextval(\'business_role_id_seq\')');

        // business_role_permission_id_seq
        $this->addSql("
        DO $$ 
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'business_role_permission_id_seq') THEN
                CREATE SEQUENCE business_role_permission_id_seq;
            END IF;
        END
        $$;
    ");
        $this->addSql('SELECT setval(\'business_role_permission_id_seq\', (SELECT COALESCE(MAX(id), 1) FROM business_role_permission))');
        $this->addSql('ALTER TABLE business_role_permission ALTER id SET DEFAULT nextval(\'business_role_permission_id_seq\')');

        // company_id_seq
        $this->addSql("
        DO $$ 
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'company_id_seq') THEN
                CREATE SEQUENCE company_id_seq;
            END IF;
        END
        $$;
    ");
        $this->addSql('SELECT setval(\'company_id_seq\', (SELECT COALESCE(MAX(id), 1) FROM company))');
        $this->addSql('ALTER TABLE company ALTER id SET DEFAULT nextval(\'company_id_seq\')');

        // employee_id_seq
        $this->addSql("
        DO $$ 
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'employee_id_seq') THEN
                CREATE SEQUENCE employee_id_seq;
            END IF;
        END
        $$;
    ");
        $this->addSql('SELECT setval(\'employee_id_seq\', (SELECT COALESCE(MAX(id), 1) FROM employee))');
        $this->addSql('ALTER TABLE employee ALTER id SET DEFAULT nextval(\'employee_id_seq\')');

        // employee_permission_id_seq
        $this->addSql("
        DO $$ 
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'employee_permission_id_seq') THEN
                CREATE SEQUENCE employee_permission_id_seq;
            END IF;
        END
        $$;
    ");
        $this->addSql('SELECT setval(\'employee_permission_id_seq\', (SELECT COALESCE(MAX(id), 1) FROM employee_permission))');
        $this->addSql('ALTER TABLE employee_permission ALTER id SET DEFAULT nextval(\'employee_permission_id_seq\')');

        // permission_id_seq
        $this->addSql("
        DO $$ 
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'permission_id_seq') THEN
                CREATE SEQUENCE permission_id_seq;
            END IF;
        END
        $$;
    ");
        $this->addSql('SELECT setval(\'permission_id_seq\', (SELECT COALESCE(MAX(id), 1) FROM permission))');
        $this->addSql('ALTER TABLE permission ALTER id SET DEFAULT nextval(\'permission_id_seq\')');

        // permission_group_id_seq
        $this->addSql("
        DO $$ 
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'permission_group_id_seq') THEN
                CREATE SEQUENCE permission_group_id_seq;
            END IF;
        END
        $$;
    ");
        $this->addSql('SELECT setval(\'permission_group_id_seq\', (SELECT COALESCE(MAX(id), 1) FROM permission_group))');
        $this->addSql('ALTER TABLE permission_group ALTER id SET DEFAULT nextval(\'permission_group_id_seq\')');

        // quickbooks_report_id_seq
        $this->addSql("
        DO $$ 
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'quickbooks_report_id_seq') THEN
                CREATE SEQUENCE quickbooks_report_id_seq;
            END IF;
        END
        $$;
    ");
        $this->addSql('SELECT setval(\'quickbooks_report_id_seq\', (SELECT COALESCE(MAX(id), 1) FROM quickbooks_report))');
        $this->addSql('ALTER TABLE quickbooks_report ALTER id SET DEFAULT nextval(\'quickbooks_report_id_seq\')');

        // user_id_seq
        $this->addSql("
        DO $$ 
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'user_id_seq') THEN
                CREATE SEQUENCE user_id_seq;
            END IF;
        END
        $$;
    ");
        $this->addSql('SELECT setval(\'user_id_seq\', (SELECT COALESCE(MAX(id), 1) FROM "user"))');
        $this->addSql('ALTER TABLE "user" ALTER id SET DEFAULT nextval(\'user_id_seq\')');
    }

    public function down(Schema $schema): void
    {
    }
}

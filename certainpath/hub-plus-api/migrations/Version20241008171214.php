<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241008171214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE application_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE application_access_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE audit_log_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE business_role_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE business_role_permission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE company_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE employee_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE employee_permission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE permission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE permission_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE quickbooks_report_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE application (id INT NOT NULL, name VARCHAR(255) NOT NULL, internal_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE application_access (id INT NOT NULL, employee_id INT NOT NULL, application_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B65A27918C03F15C ON application_access (employee_id)');
        $this->addSql('CREATE INDEX IDX_B65A27913E030ACD ON application_access (application_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B65A27918C03F15C3E030ACD ON application_access (employee_id, application_id)');
        $this->addSql('CREATE TABLE audit_log (id INT NOT NULL, author_id INT DEFAULT NULL, organization VARCHAR(255) DEFAULT NULL, entity_identifier VARCHAR(255) NOT NULL, operation_type VARCHAR(255) NOT NULL, entity_namespace VARCHAR(255) NOT NULL, request_data JSON DEFAULT NULL, event_data JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F6E1C0F5F675F31B ON audit_log (author_id)');
        $this->addSql('COMMENT ON COLUMN audit_log.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN audit_log.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE business_role (id INT NOT NULL, internal_name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE business_role_permission (id INT NOT NULL, role_id INT NOT NULL, permission_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BDFA4A99D60322AC ON business_role_permission (role_id)');
        $this->addSql('CREATE INDEX IDX_BDFA4A99FED90CCA ON business_role_permission (permission_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BDFA4A99D60322ACFED90CCA ON business_role_permission (role_id, permission_id)');
        $this->addSql('CREATE TABLE company (id INT NOT NULL, company_name VARCHAR(255) NOT NULL, salesforce_id VARCHAR(255) DEFAULT NULL, intacct_id VARCHAR(255) DEFAULT NULL, marketing_enabled BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, certain_path BOOLEAN DEFAULT false NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FBF094F79696D00 ON company (salesforce_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FBF094F72AD9E41 ON company (intacct_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FBF094FD17F50A6 ON company (uuid)');
        $this->addSql('CREATE UNIQUE INDEX unique_certain_path_company ON company (certain_path) WHERE (certain_path = true)');
        $this->addSql('CREATE TABLE employee (id INT NOT NULL, user_id INT NOT NULL, company_id INT NOT NULL, role_id INT DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, hire_date DATE DEFAULT NULL, termination_date DATE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5D9F75A1A76ED395 ON employee (user_id)');
        $this->addSql('CREATE INDEX IDX_5D9F75A1979B1AD6 ON employee (company_id)');
        $this->addSql('CREATE INDEX IDX_5D9F75A1D60322AC ON employee (role_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5D9F75A1A76ED395979B1AD6 ON employee (user_id, company_id)');
        $this->addSql('CREATE TABLE employee_permission (id INT NOT NULL, employee_id INT NOT NULL, permission_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_789E227E8C03F15C ON employee_permission (employee_id)');
        $this->addSql('CREATE INDEX IDX_789E227EFED90CCA ON employee_permission (permission_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_789E227E8C03F15CFED90CCA ON employee_permission (employee_id, permission_id)');
        $this->addSql('CREATE TABLE permission (id INT NOT NULL, permission_group_id INT NOT NULL, internal_name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, description TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, certain_path BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E04992AAB6C0CF1 ON permission (permission_group_id)');
        $this->addSql('CREATE TABLE permission_group (id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, certain_path BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE quickbooks_report (id INT NOT NULL, date DATE NOT NULL, report_id UUID NOT NULL, intacct_id VARCHAR(255) DEFAULT NULL, report_type VARCHAR(255) NOT NULL, bucket_name VARCHAR(255) NOT NULL, object_key TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D9380EE672AD9E41 ON quickbooks_report (intacct_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D9380EE672AD9E41AA9E377A4BD2A4C0FFF2BAD2 ON quickbooks_report (intacct_id, date, report_id, report_type)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, sso_id VARCHAR(255) DEFAULT NULL, salesforce_id VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6497843BFA4 ON "user" (sso_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64979696D00 ON "user" (salesforce_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649D17F50A6 ON "user" (uuid)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE application_access ADD CONSTRAINT FK_B65A27918C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE application_access ADD CONSTRAINT FK_B65A27913E030ACD FOREIGN KEY (application_id) REFERENCES application (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_F6E1C0F5F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE business_role_permission ADD CONSTRAINT FK_BDFA4A99D60322AC FOREIGN KEY (role_id) REFERENCES business_role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE business_role_permission ADD CONSTRAINT FK_BDFA4A99FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A1979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A1D60322AC FOREIGN KEY (role_id) REFERENCES business_role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_permission ADD CONSTRAINT FK_789E227E8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_permission ADD CONSTRAINT FK_789E227EFED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE permission ADD CONSTRAINT FK_E04992AAB6C0CF1 FOREIGN KEY (permission_group_id) REFERENCES permission_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE application_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE application_access_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE audit_log_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE business_role_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE business_role_permission_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE company_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE employee_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE employee_permission_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE permission_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE permission_group_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE quickbooks_report_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE application_access DROP CONSTRAINT FK_B65A27918C03F15C');
        $this->addSql('ALTER TABLE application_access DROP CONSTRAINT FK_B65A27913E030ACD');
        $this->addSql('ALTER TABLE audit_log DROP CONSTRAINT FK_F6E1C0F5F675F31B');
        $this->addSql('ALTER TABLE business_role_permission DROP CONSTRAINT FK_BDFA4A99D60322AC');
        $this->addSql('ALTER TABLE business_role_permission DROP CONSTRAINT FK_BDFA4A99FED90CCA');
        $this->addSql('ALTER TABLE employee DROP CONSTRAINT FK_5D9F75A1A76ED395');
        $this->addSql('ALTER TABLE employee DROP CONSTRAINT FK_5D9F75A1979B1AD6');
        $this->addSql('ALTER TABLE employee DROP CONSTRAINT FK_5D9F75A1D60322AC');
        $this->addSql('ALTER TABLE employee_permission DROP CONSTRAINT FK_789E227E8C03F15C');
        $this->addSql('ALTER TABLE employee_permission DROP CONSTRAINT FK_789E227EFED90CCA');
        $this->addSql('ALTER TABLE permission DROP CONSTRAINT FK_E04992AAB6C0CF1');
        $this->addSql('DROP TABLE application');
        $this->addSql('DROP TABLE application_access');
        $this->addSql('DROP TABLE audit_log');
        $this->addSql('DROP TABLE business_role');
        $this->addSql('DROP TABLE business_role_permission');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE employee');
        $this->addSql('DROP TABLE employee_permission');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE permission_group');
        $this->addSql('DROP TABLE quickbooks_report');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

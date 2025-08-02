<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250113193323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE IF EXISTS prospect_source_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS prospect_source_id_seq1 CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS report_id_seq CASCADE');
        //$this->addSql('CREATE SEQUENCE IF NOT EXISTS prospect_source_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('
            CREATE TABLE IF NOT EXISTS prospect_source (
                id SERIAL NOT NULL,
                prospect_id INT NOT NULL,
                name TEXT NOT NULL,
                license_start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                license_end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                refreshed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                previous_json TEXT DEFAULT NULL,
                current_json TEXT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id))
            ');
        $this->addSql('CREATE INDEX IDX_1F140A8ED182060A ON prospect_source (prospect_id)');
        $this->addSql('COMMENT ON COLUMN prospect_source.license_start_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN prospect_source.license_end_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN prospect_source.refreshed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN prospect_source.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN prospect_source.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE IF EXISTS prospect_source ADD CONSTRAINT FK_1F140A8ED182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE IF EXISTS report DROP CONSTRAINT fk_c42f7784979b1ad6');
        $this->addSql('ALTER TABLE IF EXISTS company_trade DROP CONSTRAINT fk_39ebe103979b1ad6');
        $this->addSql('ALTER TABLE IF EXISTS company_trade DROP CONSTRAINT fk_39ebe103c2d9760');
        $this->addSql('DROP TABLE IF EXISTS report');
        $this->addSql('DROP TABLE IF EXISTS company_trade');
        $this->addSql('ALTER TABLE IF EXISTS business_unit ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN business_unit.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE IF EXISTS campaign ADD is_active BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE IF EXISTS campaign ADD is_deleted BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE IF EXISTS company ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN company.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE IF EXISTS customer ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN customer.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE IF EXISTS email ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN email.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE IF EXISTS invoice ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN invoice.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE IF EXISTS location ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN location.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE IF EXISTS mail_package ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN mail_package.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE IF EXISTS phone ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN phone.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE IF EXISTS prospect ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        // Populate the new table with data from the existing prospects.json table
        $this->addSql("
            INSERT INTO prospect_source (prospect_id, name, current_json, created_at, updated_at)
            SELECT
                id AS prospect_id,
                'system_initialization' AS name,
                COALESCE(json::json, '{}'::json) AS current_json,
                NOW() AS created_at,
                NOW() AS updated_at
            FROM prospect
        ");

        $this->addSql('ALTER TABLE IF EXISTS prospect DROP json');
        $this->addSql('COMMENT ON COLUMN prospect.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE IF EXISTS saved_query ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN saved_query.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE IF EXISTS subscription ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN subscription.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE IF EXISTS users ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN users.deleted_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE IF EXISTS prospect_source_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS prospect_source_id_seq1 CASCADE');
        $this->addSql('CREATE SEQUENCE report_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE report (id INT NOT NULL, company_id INT NOT NULL, name TEXT NOT NULL, last_run TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_c42f7784979b1ad6 ON report (company_id)');
        $this->addSql('COMMENT ON COLUMN report.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN report.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE company_trade (company_id INT NOT NULL, trade_id INT NOT NULL, PRIMARY KEY(company_id, trade_id))');
        $this->addSql('CREATE INDEX idx_39ebe103c2d9760 ON company_trade (trade_id)');
        $this->addSql('CREATE INDEX idx_39ebe103979b1ad6 ON company_trade (company_id)');
        $this->addSql('ALTER TABLE IF EXISTS report ADD CONSTRAINT fk_c42f7784979b1ad6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE IF EXISTS company_trade ADD CONSTRAINT fk_39ebe103979b1ad6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE IF EXISTS company_trade ADD CONSTRAINT fk_39ebe103c2d9760 FOREIGN KEY (trade_id) REFERENCES trade (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE IF EXISTS prospect_source DROP CONSTRAINT FK_1F140A8ED182060A');
        $this->addSql('DROP TABLE IF EXISTS prospect_source');
        $this->addSql('ALTER TABLE IF EXISTS phone DROP deleted_at');
        $this->addSql('ALTER TABLE IF EXISTS saved_query DROP deleted_at');
        $this->addSql('ALTER TABLE IF EXISTS business_unit DROP deleted_at');
        $this->addSql('ALTER TABLE IF EXISTS prospect ADD json TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE IF EXISTS prospect DROP deleted_at');
        $this->addSql('ALTER TABLE IF EXISTS mail_package DROP deleted_at');
        $this->addSql('ALTER TABLE IF EXISTS customer DROP deleted_at');
        $this->addSql('ALTER TABLE IF EXISTS subscription DROP deleted_at');
        $this->addSql('ALTER TABLE IF EXISTS location DROP deleted_at');
        $this->addSql('ALTER TABLE IF EXISTS users DROP deleted_at');
        $this->addSql('ALTER TABLE IF EXISTS invoice DROP deleted_at');
        $this->addSql('ALTER TABLE IF EXISTS email DROP deleted_at');
        $this->addSql('ALTER TABLE IF EXISTS campaign DROP is_active');
        $this->addSql('ALTER TABLE IF EXISTS campaign DROP is_deleted');
        $this->addSql('ALTER TABLE IF EXISTS company DROP deleted_at');
    }
}

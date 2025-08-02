<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250601175503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE credit_memo (id SERIAL NOT NULL, invoice_id INT NOT NULL, cm_date TIMESTAMP(0) WITH TIME ZONE NOT NULL, status VARCHAR(255) NOT NULL, total_amount NUMERIC(10, 2) NOT NULL, reason VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AC882F172989F1FD ON credit_memo (invoice_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_AC882F17D17F50A6 ON credit_memo (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN credit_memo.cm_date IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN credit_memo.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN credit_memo.updated_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE credit_memo_line_item (id SERIAL NOT NULL, credit_memo_id INT NOT NULL, description VARCHAR(255) NOT NULL, amount NUMERIC(10, 2) NOT NULL, voucher_code VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4125874C8E574316 ON credit_memo_line_item (credit_memo_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_4125874CD17F50A6 ON credit_memo_line_item (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN credit_memo_line_item.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN credit_memo_line_item.updated_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE invoice (id SERIAL NOT NULL, company_id INT NOT NULL, event_session_id INT DEFAULT NULL, invoice_date TIMESTAMP(0) WITH TIME ZONE NOT NULL, status VARCHAR(255) NOT NULL, total_amount NUMERIC(10, 2) NOT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_90651744979B1AD6 ON invoice (company_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9065174439D135F0 ON invoice (event_session_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_90651744D17F50A6 ON invoice (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN invoice.invoice_date IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN invoice.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN invoice.updated_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE invoice_line_item (id SERIAL NOT NULL, invoice_id INT NOT NULL, description VARCHAR(255) NOT NULL, quantity INT DEFAULT 1 NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, line_total NUMERIC(10, 2) NOT NULL, discount_code VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F1F9275B2989F1FD ON invoice_line_item (invoice_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_F1F9275BD17F50A6 ON invoice_line_item (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN invoice_line_item.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN invoice_line_item.updated_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE credit_memo ADD CONSTRAINT FK_AC882F172989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE credit_memo_line_item ADD CONSTRAINT FK_4125874C8E574316 FOREIGN KEY (credit_memo_id) REFERENCES credit_memo (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD CONSTRAINT FK_90651744979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD CONSTRAINT FK_9065174439D135F0 FOREIGN KEY (event_session_id) REFERENCES event_session (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice_line_item ADD CONSTRAINT FK_F1F9275B2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE credit_memo DROP CONSTRAINT FK_AC882F172989F1FD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE credit_memo_line_item DROP CONSTRAINT FK_4125874C8E574316
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP CONSTRAINT FK_90651744979B1AD6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP CONSTRAINT FK_9065174439D135F0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice_line_item DROP CONSTRAINT FK_F1F9275B2989F1FD
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE credit_memo
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE credit_memo_line_item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE invoice
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE invoice_line_item
        SQL);
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250602102313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP SEQUENCE event_discount_ledger_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE event_voucher_ledger_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_voucher_ledger DROP CONSTRAINT fk_de9bb432bfe454a4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_voucher_ledger DROP CONSTRAINT fk_de9bb432979b1ad6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_discount_ledger DROP CONSTRAINT fk_67e8ecb37344e182
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_discount_ledger DROP CONSTRAINT fk_67e8ecb3979b1ad6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_discount_ledger DROP CONSTRAINT fk_67e8ecb3bfe454a4
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event_voucher_ledger
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event_discount_ledger
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE event_discount_ledger_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE event_voucher_ledger_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event_voucher_ledger (id SERIAL NOT NULL, company_id INT NOT NULL, event_checkout_id INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, event_voucher_id INT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_de9bb432979b1ad6 ON event_voucher_ledger (company_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_de9bb432bfe454a4 ON event_voucher_ledger (event_checkout_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_event_voucher_id ON event_voucher_ledger (event_voucher_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN event_voucher_ledger.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN event_voucher_ledger.updated_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event_discount_ledger (id SERIAL NOT NULL, event_checkout_id INT NOT NULL, company_id INT NOT NULL, discount_type_id INT DEFAULT NULL, usage VARCHAR(255) NOT NULL, code VARCHAR(255) DEFAULT NULL, amount NUMERIC(10, 2) NOT NULL, reason VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, discount_type_name VARCHAR(255) NOT NULL, intacct_id VARCHAR(255) DEFAULT NULL, event_discount_id INT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_67e8ecb37344e182 ON event_discount_ledger (discount_type_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_67e8ecb3979b1ad6 ON event_discount_ledger (company_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_event_discount_id ON event_discount_ledger (event_discount_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_67e8ecb3bfe454a4 ON event_discount_ledger (event_checkout_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN event_discount_ledger.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN event_discount_ledger.updated_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_voucher_ledger ADD CONSTRAINT fk_de9bb432bfe454a4 FOREIGN KEY (event_checkout_id) REFERENCES event_checkout (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_voucher_ledger ADD CONSTRAINT fk_de9bb432979b1ad6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_discount_ledger ADD CONSTRAINT fk_67e8ecb37344e182 FOREIGN KEY (discount_type_id) REFERENCES discount_type (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_discount_ledger ADD CONSTRAINT fk_67e8ecb3979b1ad6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_discount_ledger ADD CONSTRAINT fk_67e8ecb3bfe454a4 FOREIGN KEY (event_checkout_id) REFERENCES event_checkout (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }
}

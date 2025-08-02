<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250525151907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE payment_gateway (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE payment_transaction (id SERIAL NOT NULL, created_by_id INT NOT NULL, gateway_id INT NOT NULL, status_id INT NOT NULL, transaction_id VARCHAR(255) NOT NULL, amount NUMERIC(10, 2) NOT NULL, error_code VARCHAR(255) DEFAULT NULL, error_message TEXT DEFAULT NULL, customer_profile_id VARCHAR(255) DEFAULT NULL, payment_profile_id VARCHAR(255) DEFAULT NULL, response_data JSON DEFAULT NULL, card_type VARCHAR(255) DEFAULT NULL, card_last4 VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_84BBD50BB03A8386 ON payment_transaction (created_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_84BBD50B577F8E00 ON payment_transaction (gateway_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_84BBD50B6BF700BD ON payment_transaction (status_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_84BBD50BD17F50A6 ON payment_transaction (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_84BBD50B2FC0CB0F ON payment_transaction (transaction_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN payment_transaction.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN payment_transaction.updated_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE payment_transaction_status (id SERIAL NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_transaction ADD CONSTRAINT FK_84BBD50BB03A8386 FOREIGN KEY (created_by_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_transaction ADD CONSTRAINT FK_84BBD50B577F8E00 FOREIGN KEY (gateway_id) REFERENCES payment_gateway (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_transaction ADD CONSTRAINT FK_84BBD50B6BF700BD FOREIGN KEY (status_id) REFERENCES payment_transaction_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_transaction DROP CONSTRAINT FK_84BBD50BB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_transaction DROP CONSTRAINT FK_84BBD50B577F8E00
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_transaction DROP CONSTRAINT FK_84BBD50B6BF700BD
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payment_gateway
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payment_transaction
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payment_transaction_status
        SQL);
    }
}

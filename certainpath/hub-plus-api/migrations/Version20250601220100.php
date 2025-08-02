<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250601220100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE payment_invoice (id SERIAL NOT NULL, payment_id INT NOT NULL, invoice_id INT NOT NULL, applied_amount NUMERIC(10, 2) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_892C19AE4C3A3BB ON payment_invoice (payment_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_892C19AE2989F1FD ON payment_invoice (invoice_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN payment_invoice.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN payment_invoice.updated_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_invoice ADD CONSTRAINT FK_892C19AE4C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_invoice ADD CONSTRAINT FK_892C19AE2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_checkout ADD authnet_customer_profile_id VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_checkout ADD authnet_payment_profile_id VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_checkout ADD card_last4 VARCHAR(16) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_checkout ADD card_type VARCHAR(50) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_enrollment_waitlist ADD seat_price NUMERIC(10, 2) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_invoice DROP CONSTRAINT FK_892C19AE4C3A3BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_invoice DROP CONSTRAINT FK_892C19AE2989F1FD
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payment_invoice
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_checkout DROP authnet_customer_profile_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_checkout DROP authnet_payment_profile_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_checkout DROP card_last4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_checkout DROP card_type
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_enrollment_waitlist DROP seat_price
        SQL);
    }
}

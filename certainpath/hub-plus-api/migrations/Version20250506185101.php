<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250506185101 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->createDiscountTypeTable();
        $this->createEventDiscountTable();
        $this->createEventEventDiscountTable();
        $this->createEventVenueTable();
        $this->createEventVoucherTable();
    }

    public function down(Schema $schema): void
    {
        $this->dropEventVoucherTable();
        $this->dropEventVenueTable();
        $this->dropEventEventDiscountTable();
        $this->dropEventDiscountTable();
        $this->dropDiscountTypeTable();
    }

    private function createDiscountTypeTable(): void
    {
        $this->addSql('
            CREATE TABLE discount_type (
                id SERIAL NOT NULL,
                name TEXT NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('
            CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_125C52935E237E06
            ON discount_type (name)
        ');
    }

    private function createEventDiscountTable(): void
    {
        $this->addSql('
            CREATE TABLE event_discount (
                id SERIAL NOT NULL,
                discount_type_id INT NOT NULL,
                code TEXT NOT NULL,
                description TEXT DEFAULT NULL,
                discount_value TEXT NOT NULL,
                minimum_purchase_amount TEXT DEFAULT NULL,
                maximum_uses INT DEFAULT NULL,
                is_active BOOLEAN DEFAULT true NOT NULL,
                start_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                end_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('
            CREATE INDEX IF NOT EXISTS IDX_A70C5DDE7344E182
            ON event_discount (discount_type_id)
        ');

        $this->addSql('
            CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_A70C5DDE77153098
            ON event_discount (code)
        ');

        $this->addSql('COMMENT ON COLUMN event_discount.start_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_discount.end_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_discount.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_discount.updated_at IS \'(DC2Type:datetimetz_immutable)\'');

        $this->addSql('
            ALTER TABLE event_discount
            ADD CONSTRAINT FK_A70C5DDE7344E182
            FOREIGN KEY (discount_type_id) REFERENCES discount_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    private function createEventEventDiscountTable(): void
    {
        $this->addSql('
            CREATE TABLE event_event_discount (
                id SERIAL NOT NULL,
                event_id INT NOT NULL,
                event_discount_id INT NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('
            CREATE INDEX IF NOT EXISTS IDX_7848EBAD71F7E88B
            ON event_event_discount (event_id)
        ');

        $this->addSql('
            CREATE INDEX IF NOT EXISTS IDX_7848EBADE7F5B29F
            ON event_event_discount (event_discount_id)
        ');

        $this->addSql('
            ALTER TABLE event_event_discount
            ADD CONSTRAINT FK_7848EBAD71F7E88B
            FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');

        $this->addSql('
            ALTER TABLE event_event_discount
            ADD CONSTRAINT FK_7848EBADE7F5B29F
            FOREIGN KEY (event_discount_id) REFERENCES event_discount (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    private function createEventVenueTable(): void
    {
        $this->addSql('
            CREATE TABLE event_venue (
                id SERIAL NOT NULL,
                name TEXT NOT NULL,
                description TEXT DEFAULT NULL,
                address TEXT NOT NULL,
                address2 TEXT DEFAULT NULL,
                city TEXT NOT NULL,
                state TEXT NOT NULL,
                postal_code TEXT NOT NULL,
                country TEXT NOT NULL,
                is_active BOOLEAN NOT NULL,
                timezone TEXT NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('COMMENT ON COLUMN event_venue.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_venue.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
    }

    private function createEventVoucherTable(): void
    {
        $this->addSql('
            CREATE TABLE event_voucher (
                id SERIAL NOT NULL,
                company_id INT NOT NULL,
                name TEXT NOT NULL,
                description TEXT DEFAULT NULL,
                total_seats INT NOT NULL,
                used_seats INT NOT NULL,
                is_active BOOLEAN DEFAULT true NOT NULL,
                start_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                end_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('
            CREATE INDEX IF NOT EXISTS IDX_96C50C77979B1AD6
            ON event_voucher (company_id)
        ');

        $this->addSql('
            CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_96C50C775E237E06
            ON event_voucher (name)
        ');

        $this->addSql('COMMENT ON COLUMN event_voucher.start_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_voucher.end_date IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_voucher.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN event_voucher.updated_at IS \'(DC2Type:datetimetz_immutable)\'');

        $this->addSql('
            ALTER TABLE event_voucher
            ADD CONSTRAINT FK_96C50C77979B1AD6
            FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    private function dropDiscountTypeTable(): void
    {
        $this->addSql('DROP TABLE discount_type');
    }

    private function dropEventDiscountTable(): void
    {
        $this->addSql('ALTER TABLE event_discount DROP CONSTRAINT FK_A70C5DDE7344E182');
        $this->addSql('DROP TABLE event_discount');
    }

    private function dropEventEventDiscountTable(): void
    {
        $this->addSql('ALTER TABLE event_event_discount DROP CONSTRAINT FK_7848EBAD71F7E88B');
        $this->addSql('ALTER TABLE event_event_discount DROP CONSTRAINT FK_7848EBADE7F5B29F');
        $this->addSql('DROP TABLE event_event_discount');
    }

    private function dropEventVenueTable(): void
    {
        $this->addSql('DROP TABLE event_venue');
    }

    private function dropEventVoucherTable(): void
    {
        $this->addSql('ALTER TABLE event_voucher DROP CONSTRAINT FK_96C50C77979B1AD6');
        $this->addSql('DROP TABLE event_voucher');
    }
}

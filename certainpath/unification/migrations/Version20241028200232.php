<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241028200232 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE restricted_address_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE restricted_address (id INT NOT NULL, address1 TEXT DEFAULT NULL, address2 TEXT DEFAULT NULL, city TEXT DEFAULT NULL, state_code TEXT DEFAULT NULL, postal_code TEXT DEFAULT NULL, country_code TEXT DEFAULT NULL, is_business BOOLEAN DEFAULT false NOT NULL, is_vacant BOOLEAN DEFAULT false NOT NULL, is_verified BOOLEAN DEFAULT false NOT NULL, usps_verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, usps_verification_attempts INT DEFAULT 0 NOT NULL, usps_response TEXT DEFAULT NULL, external_id TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN restricted_address.usps_verified_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN restricted_address.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN restricted_address.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN restricted_address.processed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE address ADD is_do_not_mail BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE address ADD is_global_do_not_mail BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE address ADD is_verified BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE restricted_address_id_seq CASCADE');
        $this->addSql('DROP TABLE restricted_address');
        $this->addSql('ALTER TABLE address DROP is_do_not_mail');
        $this->addSql('ALTER TABLE address DROP is_global_do_not_mail');
        $this->addSql('ALTER TABLE address DROP is_verified');
    }
}

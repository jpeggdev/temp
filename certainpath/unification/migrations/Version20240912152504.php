<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240912152504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE user_account (user_id INT NOT NULL, account_id INT NOT NULL, PRIMARY KEY(user_id, account_id))');
        $this->addSql('CREATE INDEX idx_253b48ae9b6b5fba ON user_account (account_id)');
        $this->addSql('CREATE INDEX idx_253b48aea76ed395 ON user_account (user_id)');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE account (id INT NOT NULL, identifier TEXT NOT NULL, name TEXT NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_7d3656a4772e836a ON account (identifier)');
        $this->addSql('COMMENT ON COLUMN account.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN account.updated IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE prospect (id INT NOT NULL, account_id INT NOT NULL, full_name TEXT NOT NULL, first_name TEXT DEFAULT NULL, last_name TEXT DEFAULT NULL, address1 TEXT NOT NULL, city TEXT NOT NULL, state TEXT NOT NULL, postal_code TEXT NOT NULL, postal_code_short TEXT NOT NULL, do_not_mail BOOLEAN DEFAULT false NOT NULL, do_not_contact BOOLEAN DEFAULT false NOT NULL, json TEXT NOT NULL, external_id TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX prospect_account_external_uniq ON prospect (account_id, external_id)');
        $this->addSql('CREATE INDEX prospect_account_state_idx ON prospect (account_id, state)');
        $this->addSql('CREATE INDEX prospect_account_city_idx ON prospect (account_id, city)');
        $this->addSql('CREATE INDEX prospect_account_postal_code_short_idx ON prospect (account_id, postal_code_short)');
        $this->addSql('CREATE INDEX prospect_account_postal_code_idx ON prospect (account_id, postal_code)');
        $this->addSql('CREATE INDEX idx_c9ce8c7d9b6b5fba ON prospect (account_id)');
        $this->addSql('COMMENT ON COLUMN prospect.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN prospect.updated IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE users (id INT NOT NULL, identifier TEXT NOT NULL, access_roles TEXT NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_1483a5e9772e836a ON users (identifier)');
        $this->addSql('COMMENT ON COLUMN users.access_roles IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN users.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN users.updated IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE business_unit (id INT NOT NULL, account_id INT NOT NULL, name TEXT NOT NULL, external_id TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_8c200e5e9b6b5fba ON business_unit (account_id)');
        $this->addSql('COMMENT ON COLUMN business_unit.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN business_unit.updated IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE phone (id INT NOT NULL, account_id INT NOT NULL, country_code TEXT NOT NULL, number TEXT NOT NULL, extension TEXT DEFAULT NULL, external_id TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_444f97dd9b6b5fba ON phone (account_id)');
        $this->addSql('COMMENT ON COLUMN phone.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN phone.updated IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE mail_package (id INT NOT NULL, prospect_id INT NOT NULL, mail_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name TEXT NOT NULL, series TEXT NOT NULL, external_id TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX mail_package_name_idx ON mail_package (name)');
        $this->addSql('CREATE INDEX idx_4d5e6fad182060a ON mail_package (prospect_id)');
        $this->addSql('COMMENT ON COLUMN mail_package.mail_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail_package.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail_package.updated IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE invoice (id INT NOT NULL, account_id INT NOT NULL, location_id INT DEFAULT NULL, subscription_id INT DEFAULT NULL, business_unit_id INT DEFAULT NULL, identifier TEXT NOT NULL, total TEXT NOT NULL, balance TEXT NOT NULL, external_id TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX invoice_account_external_uniq ON invoice (account_id, external_id)');
        $this->addSql('CREATE INDEX invoice_account_identifier_idx ON invoice (account_id, identifier)');
        $this->addSql('CREATE INDEX idx_90651744a58ecb40 ON invoice (business_unit_id)');
        $this->addSql('CREATE INDEX idx_906517449a1887dc ON invoice (subscription_id)');
        $this->addSql('CREATE INDEX idx_9065174464d218e ON invoice (location_id)');
        $this->addSql('CREATE INDEX idx_906517449b6b5fba ON invoice (account_id)');
        $this->addSql('COMMENT ON COLUMN invoice.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN invoice.updated IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE location (id INT NOT NULL, account_id INT NOT NULL, physical_address_id INT DEFAULT NULL, primary_email_id INT DEFAULT NULL, primary_phone_id INT DEFAULT NULL, business_unit_id INT DEFAULT NULL, name TEXT NOT NULL, description TEXT DEFAULT NULL, external_id TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_5e9e89cba58ecb40 ON location (business_unit_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_5e9e89cb1a0c4e3a ON location (primary_phone_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_5e9e89cb894dac38 ON location (primary_email_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_5e9e89cb6646778d ON location (physical_address_id)');
        $this->addSql('CREATE INDEX idx_5e9e89cb9b6b5fba ON location (account_id)');
        $this->addSql('COMMENT ON COLUMN location.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN location.updated IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_75ea56e016ba31db ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX idx_75ea56e0e3bd61ce ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX idx_75ea56e0fb7336f0 ON messenger_messages (queue_name)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE subscription (id INT NOT NULL, account_id INT NOT NULL, name TEXT NOT NULL, starts_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ends_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, frequency TEXT NOT NULL, price TEXT NOT NULL, external_id TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_a3c664d39b6b5fba ON subscription (account_id)');
        $this->addSql('COMMENT ON COLUMN subscription.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN subscription.updated IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE email (id INT NOT NULL, account_id INT NOT NULL, email_address TEXT NOT NULL, external_id TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_e7927c749b6b5fba ON email (account_id)');
        $this->addSql('COMMENT ON COLUMN email.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN email.updated IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE address (id INT NOT NULL, account_id INT NOT NULL, address1 TEXT DEFAULT NULL, address2 TEXT DEFAULT NULL, city TEXT DEFAULT NULL, state_code TEXT DEFAULT NULL, postal_code TEXT DEFAULT NULL, country_code TEXT DEFAULT NULL, external_id TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_d4e6f819b6b5fba ON address (account_id)');
        $this->addSql('COMMENT ON COLUMN address.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN address.updated IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE saved_query (id INT NOT NULL, account_id INT NOT NULL, dql TEXT NOT NULL, record_count INT DEFAULT NULL, last_run TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, entity_type TEXT NOT NULL, name TEXT NOT NULL, parameters TEXT NOT NULL, first_result INT DEFAULT NULL, max_results INT DEFAULT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_496e6ef29b6b5fba ON saved_query (account_id)');
        $this->addSql('COMMENT ON COLUMN saved_query.last_run IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN saved_query.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN saved_query.updated IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE customer (id INT NOT NULL, prospect_id INT DEFAULT NULL, account_id INT NOT NULL, name TEXT NOT NULL, external_id TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_81398e099b6b5fba ON customer (account_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_81398e09d182060a ON customer (prospect_id)');
        $this->addSql('COMMENT ON COLUMN customer.created IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN customer.updated IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE user_account');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE account');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE prospect');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE users');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE business_unit');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE phone');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE mail_package');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE invoice');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE location');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE messenger_messages');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE subscription');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE email');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE address');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE saved_query');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE customer');
    }
}

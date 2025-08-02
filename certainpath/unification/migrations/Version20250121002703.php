<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250121002703 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Update the address table  (add postal_code_short column)
        // Derive the value from the address.postal_code column
        $this->addSql('ALTER TABLE IF EXISTS address ADD postal_code_short TEXT DEFAULT NULL');
        $this->addSql(
            "UPDATE address 
             SET postal_code_short = UPPER(SUBSTRING(REGEXP_REPLACE(postal_code, '\\W+', '', 'g') FROM 1 FOR 5)) 
             WHERE postal_code IS NOT NULL"
        );

        // Update the restricted_address table (add postal_code_short column)
        // Derive the value from the address.postal_code column
        $this->addSql('ALTER TABLE IF EXISTS restricted_address ADD postal_code_short TEXT DEFAULT NULL');
        $this->addSql(
            "UPDATE restricted_address 
             SET postal_code_short = UPPER(SUBSTRING(REGEXP_REPLACE(postal_code, '\\W+', '', 'g') FROM 1 FOR 5)) 
             WHERE postal_code IS NOT NULL"
        );

        // Update the prospect table (add preferred_address_id column)
        // Set the most recent verified address as the preferred address
        $this->addSql('ALTER TABLE IF EXISTS prospect ADD preferred_address_id INT DEFAULT NULL');
        $this->addSql('
            ALTER TABLE prospect
            ADD CONSTRAINT FK_C9CE8C7D38BDB25C
            FOREIGN KEY (preferred_address_id)
            REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('CREATE INDEX IDX_C9CE8C7D38BDB25C ON prospect (preferred_address_id)');
        $this->updateProspectPreferredAddressed();
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE address DROP COLUMN IF EXISTS postal_code_short');
        $this->addSql('ALTER TABLE restricted_address DROP COLUMN IF EXISTS postal_code_short');
        $this->addSql('ALTER TABLE prospect DROP CONSTRAINT IF EXISTS FK_C9CE8C7D38BDB25C');
        $this->addSql('DROP INDEX IF EXISTS UNIQ_C9CE8C7D38BDB25C');
        $this->addSql('ALTER TABLE prospect DROP COLUMN IF EXISTS preferred_address_id');
    }

    private function updateProspectPreferredAddressed(): void
    {
        $this->addSql('
        UPDATE prospect
        SET preferred_address_id = (
            SELECT address.id
            FROM address
            INNER JOIN prospect_address ON prospect_address.address_id = address.id
            WHERE prospect_address.prospect_id = prospect.id
            AND address.verified_at IS NOT NULL
            AND address.is_active = true
            AND address.is_deleted = false
            ORDER BY address.verified_at DESC
            LIMIT 1
        )
        WHERE EXISTS (
            SELECT 1
            FROM address
            INNER JOIN prospect_address ON prospect_address.address_id = address.id
            WHERE prospect_address.prospect_id = prospect.id
            AND address.verified_at IS NOT NULL
            AND address.is_active = true
            AND address.is_deleted = false
        )
    ');
    }
}

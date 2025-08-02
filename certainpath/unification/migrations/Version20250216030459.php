<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216030459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("--- Lock tables
        LOCK TABLE address, prospect, prospect_address IN ACCESS EXCLUSIVE MODE;
        ");

        /*--- Update Prospect records to consolidate addresses ---*/

        $this->addSql("-- Reassign prospect.preferred_address_id entries
        WITH duplicate_addresses AS (
            SELECT id, company_id, external_id, 
                   MIN(id) OVER (PARTITION BY company_id, external_id) AS keep_id
            FROM address
        )
        UPDATE prospect p
        SET preferred_address_id = da.keep_id
        FROM duplicate_addresses da
        WHERE p.preferred_address_id = da.id AND da.id <> da.keep_id");

        /*--- Update ProspectAddress records to consolidate addresses ---*/

        $this->addSql("    -- Drop the primary key constraint
        ALTER TABLE prospect_address DROP CONSTRAINT prospect_address_pkey");

        $this->addSql("    -- Consolidate prospect_address records
        WITH duplicate_addresses AS (
            SELECT id, company_id, external_id, 
                   MIN(id) OVER (PARTITION BY company_id, external_id) AS keep_id
            FROM address
        ),
        conflicting AS (
            SELECT pa.prospect_id, pa.address_id, da.keep_id
            FROM prospect_address pa
            JOIN duplicate_addresses da ON pa.address_id = da.id
            WHERE EXISTS (
                SELECT 1 FROM prospect_address pa2
                WHERE pa2.prospect_id = pa.prospect_id
                  AND pa2.address_id = da.keep_id
            )
        )
        -- Delete duplicate prospect_address entries
        DELETE FROM prospect_address
        WHERE (prospect_id, address_id) IN (
            SELECT prospect_id, address_id FROM conflicting
        )");

        $this->addSql("-- Update remaining prospect_address entries
        WITH duplicate_addresses AS (
            SELECT id, company_id, external_id, 
                   MIN(id) OVER (PARTITION BY company_id, external_id) AS keep_id
            FROM address
        )
        UPDATE prospect_address pa
        SET address_id = da.keep_id
        FROM duplicate_addresses da
        WHERE pa.address_id = da.id AND da.id <> da.keep_id");

        $this->addSql("-- De-duplicate prospect_address records so we can re-instate the primary key constraint
        WITH duplicates AS (
            SELECT ctid FROM (
                SELECT ctid,
                       ROW_NUMBER() OVER (PARTITION BY prospect_id, address_id ORDER BY ctid) AS row_num
                FROM prospect_address
            ) AS subquery
            WHERE row_num > 1
        )
        DELETE FROM prospect_address WHERE ctid IN (SELECT ctid FROM duplicates)");

        $this->addSql("-- Re-instate the primary key constraint
        ALTER TABLE prospect_address 
        ADD CONSTRAINT prospect_address_pkey PRIMARY KEY (prospect_id, address_id)");

        $this->addSql("-- Delete Address records that are not in use
        DELETE FROM address a
        WHERE NOT EXISTS (
            SELECT 1 FROM prospect_address pa WHERE pa.address_id = a.id
        )
        AND NOT EXISTS (
            SELECT 1 FROM prospect p WHERE p.preferred_address_id = a.id
        );");

        $this->addSql("-- Resolve prospect.preferred_address_id and prospect_address
        INSERT INTO prospect_address (prospect_id, address_id)
        SELECT p.id, p.preferred_address_id
        FROM prospect p
        WHERE p.preferred_address_id IS NOT NULL
        ON CONFLICT (prospect_id, address_id) DO NOTHING");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('This migration is not reversible.');
    }
}

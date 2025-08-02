<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250106213144 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Create a new table to store the prospect details
        $this->addSql('
            CREATE TABLE IF NOT EXISTS prospect_details (
                id SERIAL NOT NULL,
                prospect_id INT NOT NULL,
                age INT DEFAULT NULL,
                info_base TEXT DEFAULT NULL,
                year_built INT DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id))
        ');
        $this->addSql('COMMENT ON COLUMN prospect_details.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN prospect_details.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B16A1B5ED182060A ON prospect_details (prospect_id)');
        $this->addSql('
            ALTER TABLE prospect_details
            ADD CONSTRAINT FK_B16A1B5ED182060A
            FOREIGN KEY (prospect_id)
            REFERENCES prospect (id)
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');

        // Populate the new table with data from the existing prospects.json table
        $this->addSql("
            INSERT INTO prospect_details (prospect_id, age, info_base, year_built, created_at, updated_at)
            SELECT
                id AS prospect_id,
                CASE 
                    WHEN (json::JSONB ->> 'age') ~ '^\d+$' THEN (json::JSONB ->> 'age')::INTEGER
                    END AS age,
                (json::JSONB ->> 'infobase') AS infobase,
                CASE 
                    WHEN (json::JSONB ->> 'yearbuilt') ~ '^\d+$' THEN (json::JSONB ->> 'yearbuilt')::INTEGER
                    END AS year_built,
                NOW(),
                NOW()
            FROM prospect
            WHERE json IS NOT NULL
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE IF EXISTS prospect_details DROP CONSTRAINT FK_B16A1B5ED182060A');
        $this->addSql('DROP TABLE IF EXISTS prospect_details');
    }
}

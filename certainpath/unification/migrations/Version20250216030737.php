<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216030737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
        UPDATE prospect
        SET is_preferred = false
        ");

        $this->addSql("
        WITH ranked_prospects AS (
            SELECT 
                pa.address_id, 
                p.id AS prospect_id,
                p.is_preferred,
                ROW_NUMBER() OVER (PARTITION BY pa.address_id ORDER BY p.created_at DESC) AS rank
            FROM prospect_address pa
            JOIN prospect p ON pa.prospect_id = p.id
            WHERE p.is_deleted = FALSE
        )
        UPDATE prospect p
        SET is_preferred = true
        FROM ranked_prospects rp
        WHERE p.id = rp.prospect_id
        AND rp.rank = 1
        ");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('This migration is not reversible.');
    }
}

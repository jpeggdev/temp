<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250721225547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add postage_processed_file table for SFTP batch processing audit trail';
    }

    public function up(Schema $schema): void
    {
        // Create postage_processed_file table for SFTP batch processing audit trail
        $this->addSql(<<<'SQL'
            CREATE TABLE postage_processed_file (id SERIAL NOT NULL, filename VARCHAR(255) NOT NULL, file_hash VARCHAR(32) NOT NULL, processed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, records_processed INT DEFAULT 0 NOT NULL, status VARCHAR(20) DEFAULT 'SUCCESS' NOT NULL, error_message TEXT DEFAULT NULL, metadata JSON DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_filename_hash ON postage_processed_file (filename, file_hash)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN postage_processed_file.processed_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN postage_processed_file.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN postage_processed_file.updated_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Drop postage_processed_file table
        $this->addSql(<<<'SQL'
            DROP TABLE postage_processed_file
        SQL);
    }
}

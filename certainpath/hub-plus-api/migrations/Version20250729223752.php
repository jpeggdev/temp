<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250729223752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create servicetitan_sync_logs table for tracking synchronization operations';
    }

    public function up(Schema $schema): void
    {
        // Create ServiceTitan sync logs table
        $this->addSql(<<<'SQL'
            CREATE TABLE servicetitan_sync_logs (
                id SERIAL NOT NULL, 
                service_titan_credential_id INT NOT NULL, 
                sync_type VARCHAR(255) NOT NULL, 
                data_type VARCHAR(255) NOT NULL, 
                status VARCHAR(255) NOT NULL, 
                started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
                records_processed INT NOT NULL, 
                records_successful INT NOT NULL, 
                records_failed INT NOT NULL, 
                error_message TEXT DEFAULT NULL, 
                error_details JSON DEFAULT NULL, 
                processing_time_seconds INT DEFAULT NULL, 
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                uuid UUID NOT NULL, 
                PRIMARY KEY(id)
            )
        SQL);
        
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6130E44BE19A4DEC ON servicetitan_sync_logs (service_titan_credential_id)
        SQL);
        
        $this->addSql(<<<'SQL'
            ALTER TABLE servicetitan_sync_logs 
            ADD CONSTRAINT FK_6130E44BE19A4DEC 
            FOREIGN KEY (service_titan_credential_id) 
            REFERENCES servicetitan_credentials (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Drop ServiceTitan sync logs table and related constraints
        $this->addSql(<<<'SQL'
            ALTER TABLE servicetitan_sync_logs DROP CONSTRAINT FK_6130E44BE19A4DEC
        SQL);
        
        $this->addSql(<<<'SQL'
            DROP TABLE servicetitan_sync_logs
        SQL);
    }
}

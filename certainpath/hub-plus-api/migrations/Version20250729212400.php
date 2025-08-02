<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * ServiceTitan Credential Entity Migration
 */
final class Version20250729212400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ServiceTitan Credential entity table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE servicetitan_credentials (
                id SERIAL NOT NULL,
                company_id INT NOT NULL,
                environment VARCHAR(255) NOT NULL,
                client_id TEXT,
                client_secret TEXT,
                access_token TEXT,
                refresh_token TEXT,
                token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                connection_status VARCHAR(255) NOT NULL DEFAULT 'inactive',
                last_connection_attempt TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                uuid UUID NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_company_environment ON servicetitan_credentials (company_id, environment)
        SQL);
        
        $this->addSql(<<<'SQL'
            ALTER TABLE servicetitan_credentials ADD CONSTRAINT fk_servicetitan_company FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE servicetitan_credentials');
    }
}
<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241011155232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Check if the sequence already exists before creating it
        $this->addSql("
        DO $$ 
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'application_id_seq') THEN
                CREATE SEQUENCE application_id_seq;
            END IF;
        END
        $$;
    ");

        // Set the sequence value to the max id from the application table
        $this->addSql('SELECT setval(\'application_id_seq\', (SELECT COALESCE(MAX(id), 1) FROM application))');

        // Alter the table to set the default value for id to use the sequence
        $this->addSql('ALTER TABLE application ALTER id SET DEFAULT nextval(\'application_id_seq\')');
    }

    public function down(Schema $schema): void
    {
        // Drop the sequence in the down migration if it exists
        $this->addSql("
        DO $$ 
        BEGIN
            IF EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'application_id_seq') THEN
                DROP SEQUENCE application_id_seq;
            END IF;
        END
        $$;
    ");

        // Remove the default value for id
        $this->addSql('ALTER TABLE application ALTER id DROP DEFAULT');
    }
}

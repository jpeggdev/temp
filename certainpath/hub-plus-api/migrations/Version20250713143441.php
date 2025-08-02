<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250713143441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP SEQUENCE trigger_debug_log_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE file_delete_job (id SERIAL NOT NULL, status VARCHAR(255) NOT NULL, progress_percent NUMERIC(5, 2) DEFAULT NULL, total_files INT DEFAULT 0 NOT NULL, processed_files INT DEFAULT 0 NOT NULL, successful_deletes INT DEFAULT 0 NOT NULL, file_uuids JSON DEFAULT NULL, failed_items JSON DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_9CC69463D17F50A6 ON file_delete_job (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN file_delete_job.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN file_delete_job.updated_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE trigger_debug_log
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE trigger_debug_log_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE trigger_debug_log (id SERIAL NOT NULL, "timestamp" TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP, operation VARCHAR(50) DEFAULT NULL, node_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, file_size BIGINT DEFAULT NULL, message TEXT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE file_delete_job
        SQL);
    }
}

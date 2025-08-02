<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\BatchStatus;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250310184940 extends AbstractMigration
{
    private const BATCH_STATUSES = [
        BatchStatus::STATUS_NEW,
        BatchStatus::STATUS_PAUSED,
        BatchStatus::STATUS_ARCHIVED,
        BatchStatus::STATUS_SENT,
        BatchStatus::STATUS_PROCESSED,
        BatchStatus::STATUS_INVOICED,
        BatchStatus::STATUS_COMPLETE,
    ];

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE bulk_batch_status_event (
                id SERIAL NOT NULL,
                batch_status_id INT NOT NULL,
                year INT NOT NULL,
                week INT NOT NULL,
                updated_batches JSONB DEFAULT \'[]\' NOT NULL,
                PRIMARY KEY(id))
        ');

        $this->addSql('
            CREATE INDEX IDX_F2F327FF26CE8F8B
            ON bulk_batch_status_event (batch_status_id)
        ');

        $this->addSql('
            CREATE INDEX bulk_batch_status_event_year_week_idx 
            ON bulk_batch_status_event (year, week)
        ');

        $this->addSql('
            CREATE UNIQUE INDEX bulk_batch_status_event_year_week_status_uniq
            ON bulk_batch_status_event (year, week, batch_status_id)
        ');

        $this->addSql('
            ALTER TABLE bulk_batch_status_event
            ADD CONSTRAINT FK_F2F327FF26CE8F8B
            FOREIGN KEY (batch_status_id) REFERENCES batch_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');

        foreach (self::BATCH_STATUSES as $batchStatus) {
            $this->addSql('
                INSERT INTO batch_status (name, created_at, updated_at)
                VALUES (:batchStatus, now(), now())
                ON CONFLICT (name) DO NOTHING
            ', ['batchStatus' => $batchStatus]);
        }

        $this->addSql("DELETE FROM batch_status WHERE name = 'uploaded'");

    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bulk_batch_status_event DROP CONSTRAINT FK_F2F327FF26CE8F8B');
        $this->addSql('DROP TABLE bulk_batch_status_event');
    }
}

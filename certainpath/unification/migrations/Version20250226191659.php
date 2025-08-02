<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\BatchStatus;
use App\Entity\CampaignIterationStatus;
use App\Entity\EventStatus;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250226191659 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE UNIQUE INDEX IF NOT EXISTS event_status_name_uniq
            ON event_status (name)
        ");

        $this->addSql("
            CREATE UNIQUE INDEX IF NOT EXISTS campaign_iteration_status_name_uniq
            ON campaign_iteration_status (name)
        ");

        $this->addSql("
            CREATE UNIQUE INDEX IF NOT EXISTS batch_status_name_uniq
            ON batch_status (name)
        ");

        $this->addSql('
            INSERT INTO event_status (name)
            VALUES (?) 
            ON CONFLICT (name) DO NOTHING
        ', [EventStatus::PAUSED]);

        $this->addSql('
            INSERT INTO batch_status (name, created_at, updated_at)
            VALUES (?, NOW(), NOW()) 
            ON CONFLICT (name) DO NOTHING
        ', [BatchStatus::STATUS_PAUSED]);

        $this->addSql('
            INSERT INTO campaign_iteration_status (name, created_at, updated_at)
            VALUES (?, NOW(), NOW()) 
            ON CONFLICT (name) DO NOTHING
        ', [CampaignIterationStatus::STATUS_PENDING]);

        $this->addSql('
            INSERT INTO campaign_iteration_status (name, created_at, updated_at)
            VALUES (?, NOW(), NOW()) 
            ON CONFLICT (name) DO NOTHING
        ', [CampaignIterationStatus::STATUS_PAUSED]);

        $this->addSql('
            INSERT INTO campaign_iteration_status (name, created_at, updated_at)
            VALUES (?, NOW(), NOW()) 
            ON CONFLICT (name) DO NOTHING
        ', [CampaignIterationStatus::STATUS_ARCHIVED]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS event_status_name_uniq');
        $this->addSql('DROP INDEX IF EXISTS batch_status_name_uniq');
        $this->addSql('DROP INDEX IF EXISTS campaign_iteration_status_name_uniq');
    }
}

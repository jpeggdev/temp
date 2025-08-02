<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\EventStatus;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250303121135 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            INSERT INTO event_status (name)
            VALUES (?) 
            ON CONFLICT (name) DO NOTHING
        ', [EventStatus::ACTIVE]);

        $this->addSql('
            INSERT INTO event_status (name)
            VALUES (?) 
            ON CONFLICT (name) DO NOTHING
        ', [EventStatus::RESUMING]);

        $this->addSql('DROP INDEX idx_f80b52d45219457f');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F80B52D45219457F ON batch (campaign_iteration_week_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_F80B52D45219457F');
        $this->addSql('CREATE INDEX idx_f80b52d45219457f ON batch (campaign_iteration_week_id)');
    }
}

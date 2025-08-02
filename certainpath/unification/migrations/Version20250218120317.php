<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\BatchStatus;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250218120317 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            INSERT INTO batch_status (name, created_at, updated_at) 
            VALUES (?, NOW(), NOW())
        ', [BatchStatus::STATUS_ARCHIVED]);

        $this->addSql('
            CREATE UNIQUE INDEX IF NOT EXISTS name_uniq
            ON batch_status (name)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DELETE FROM batch_status WHERE name = ?
        ', [BatchStatus::STATUS_ARCHIVED]);

        $this->addSql('DROP INDEX IF EXISTS name_uniq');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250522162155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the nullable `identifier` column to `timezone`, assigns IANA values, then sets it to NOT NULL.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE timezone ADD identifier VARCHAR(255)');
        $this->addSql("UPDATE timezone SET identifier = 'America/New_York'    WHERE name = 'Eastern Time (ET)'");
        $this->addSql("UPDATE timezone SET identifier = 'America/Chicago'     WHERE name = 'Central Time (CT)'");
        $this->addSql("UPDATE timezone SET identifier = 'America/Denver'      WHERE name = 'Mountain Time (MT)'");
        $this->addSql("UPDATE timezone SET identifier = 'America/Los_Angeles' WHERE name = 'Pacific Time (PT)'");
        $this->addSql("UPDATE timezone SET identifier = 'America/Anchorage'   WHERE name = 'Alaska Time (AKT)'");
        $this->addSql("UPDATE timezone SET identifier = 'Pacific/Honolulu'    WHERE name = 'Hawaii-Aleutian Time (HAT)'");
        $this->addSql('ALTER TABLE timezone ALTER COLUMN identifier SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE timezone DROP identifier');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250216160335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert default Application data (Hub, Training Portal, etc.) directly into the application table, skipping rows that already exist.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO application (name, internal_name)
            SELECT 'Hub', 'hub'
            WHERE NOT EXISTS (
                SELECT 1 FROM application WHERE internal_name = 'hub'
            )
        ");
        $this->addSql("
            INSERT INTO application (name, internal_name)
            SELECT 'Training Portal', 'training_portal'
            WHERE NOT EXISTS (
                SELECT 1 FROM application WHERE internal_name = 'training_portal'
            )
        ");
        $this->addSql("
            INSERT INTO application (name, internal_name)
            SELECT 'Stochastic', 'stochastic'
            WHERE NOT EXISTS (
                SELECT 1 FROM application WHERE internal_name = 'stochastic'
            )
        ");
        $this->addSql("
            INSERT INTO application (name, internal_name)
            SELECT 'Partner Network', 'partner_network'
            WHERE NOT EXISTS (
                SELECT 1 FROM application WHERE internal_name = 'partner_network'
            )
        ");
        $this->addSql("
            INSERT INTO application (name, internal_name)
            SELECT 'Scoreboard', 'scoreboard'
            WHERE NOT EXISTS (
                SELECT 1 FROM application WHERE internal_name = 'scoreboard'
            )
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            DELETE FROM application
            WHERE internal_name IN (
                'hub',
                'training_portal',
                'stochastic',
                'partner_network',
                'scoreboard'
            )
        ");
    }
}

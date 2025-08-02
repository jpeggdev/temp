<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250318173629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Updates the internal_name to event_registration for the record where name = "Event Registration".';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            UPDATE application
            SET internal_name = 'event_registration'
            WHERE name = 'Event Registration'
        ");
    }

    public function down(Schema $schema): void
    {
    }
}

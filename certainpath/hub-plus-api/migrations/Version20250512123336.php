<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512123336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_enrollment ADD first_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event_enrollment ADD last_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event_enrollment ADD email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event_enrollment ADD special_requests TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_enrollment ALTER employee_id DROP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_83320ECEE7927C7439D135F0 ON event_enrollment (email, event_session_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_83320ECEE7927C7439D135F0');
        $this->addSql('ALTER TABLE event_enrollment DROP first_name');
        $this->addSql('ALTER TABLE event_enrollment DROP last_name');
        $this->addSql('ALTER TABLE event_enrollment DROP email');
        $this->addSql('ALTER TABLE event_enrollment DROP special_requests');
        $this->addSql('ALTER TABLE event_enrollment ALTER employee_id SET NOT NULL');
    }
}

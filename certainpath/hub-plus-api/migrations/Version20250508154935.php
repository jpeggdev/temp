<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508154935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_checkout ADD company_id INT NOT NULL');
        $this->addSql('ALTER TABLE event_checkout ADD CONSTRAINT FK_E9D4C49E979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E9D4C49E979B1AD6 ON event_checkout (company_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_checkout DROP CONSTRAINT FK_E9D4C49E979B1AD6');
        $this->addSql('DROP INDEX IDX_E9D4C49E979B1AD6');
        $this->addSql('ALTER TABLE event_checkout DROP company_id');
    }
}

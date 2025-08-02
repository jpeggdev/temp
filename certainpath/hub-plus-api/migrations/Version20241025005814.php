<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241025005814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO field_service_software (name) VALUES ('Other')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM field_service_software WHERE name = 'Other'");
    }
}

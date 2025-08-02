<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241019190824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add FieldServiceSoftware entries for FieldEdge, ServiceTitan, and SuccessWare';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO field_service_software (name) VALUES ('FieldEdge')");
        $this->addSql("INSERT INTO field_service_software (name) VALUES ('ServiceTitan')");
        $this->addSql("INSERT INTO field_service_software (name) VALUES ('SuccessWare')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM field_service_software WHERE name IN ('FieldEdge', 'ServiceTitan', 'SuccessWare')");
    }
}

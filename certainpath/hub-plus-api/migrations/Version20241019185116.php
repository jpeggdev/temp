<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241019185116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE field_service_software_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE field_service_software (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE company ADD field_service_software_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F20A06FB3 FOREIGN KEY (field_service_software_id) REFERENCES field_service_software (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_4FBF094F20A06FB3 ON company (field_service_software_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company DROP CONSTRAINT FK_4FBF094F20A06FB3');
        $this->addSql('DROP SEQUENCE field_service_software_id_seq CASCADE');
        $this->addSql('DROP TABLE field_service_software');
        $this->addSql('DROP INDEX IDX_4FBF094F20A06FB3');
        $this->addSql('ALTER TABLE company DROP field_service_software_id');
    }
}

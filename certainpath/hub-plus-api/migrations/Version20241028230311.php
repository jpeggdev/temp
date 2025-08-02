<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241028230311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company ADD address_line1 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD address_line2 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD city VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD state VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD country VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD zip_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD is_mailing_address_same BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE company ADD mailing_address_line1 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD mailing_address_line2 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD mailing_state VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD mailing_country VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD mailing_zip_code VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company DROP address_line1');
        $this->addSql('ALTER TABLE company DROP address_line2');
        $this->addSql('ALTER TABLE company DROP city');
        $this->addSql('ALTER TABLE company DROP state');
        $this->addSql('ALTER TABLE company DROP country');
        $this->addSql('ALTER TABLE company DROP zip_code');
        $this->addSql('ALTER TABLE company DROP is_mailing_address_same');
        $this->addSql('ALTER TABLE company DROP mailing_address_line1');
        $this->addSql('ALTER TABLE company DROP mailing_address_line2');
        $this->addSql('ALTER TABLE company DROP mailing_state');
        $this->addSql('ALTER TABLE company DROP mailing_country');
        $this->addSql('ALTER TABLE company DROP mailing_zip_code');
    }
}

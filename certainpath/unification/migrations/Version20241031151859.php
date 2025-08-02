<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241031151859 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE address RENAME COLUMN usps_response TO api_response');
        $this->addSql('ALTER TABLE address DROP is_verified');
        $this->addSql('ALTER TABLE address RENAME COLUMN usps_verified_at TO verified_at');
        $this->addSql('ALTER TABLE address RENAME COLUMN usps_verification_attempts TO verification_attempts');
        $this->addSql('ALTER TABLE address ADD COLUMN api_type TEXT DEFAULT NULL');

        $this->addSql('ALTER TABLE restricted_address RENAME COLUMN usps_response TO api_response');
        $this->addSql('ALTER TABLE restricted_address DROP is_verified');
        $this->addSql('ALTER TABLE restricted_address RENAME COLUMN usps_verified_at TO verified_at');
        $this->addSql('ALTER TABLE restricted_address RENAME COLUMN usps_verification_attempts TO verification_attempts');
        $this->addSql('ALTER TABLE restricted_address ADD COLUMN api_type TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE address RENAME COLUMN api_response TO usps_response');
        $this->addSql('ALTER TABLE address ADD is_verified BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE address RENAME COLUMN verified_at TO usps_verified_at');
        $this->addSql('ALTER TABLE address RENAME COLUMN verification_attempts TO usps_verification_attempts');
        $this->addSql('ALTER TABLE address DROP COLUMN api_type');

        $this->addSql('ALTER TABLE restricted_address RENAME COLUMN api_response TO usps_response');
        $this->addSql('ALTER TABLE restricted_address ADD is_verified BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE restricted_address RENAME COLUMN verified_at TO usps_verified_at');
        $this->addSql('ALTER TABLE restricted_address RENAME COLUMN verification_attempts TO usps_verification_attempts');
        $this->addSql('ALTER TABLE restricted_address DROP COLUMN api_type');
    }
}

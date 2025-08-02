<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241017102221 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE address RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE address RENAME COLUMN updated TO updated_at');

        $this->addSql('ALTER TABLE business_unit RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE business_unit RENAME COLUMN updated TO updated_at');

        $this->addSql('ALTER TABLE company RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE company RENAME COLUMN updated TO updated_at');

        $this->addSql('ALTER TABLE customer RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE customer RENAME COLUMN updated TO updated_at');

        $this->addSql('ALTER TABLE email RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE email RENAME COLUMN updated TO updated_at');

        $this->addSql('ALTER TABLE invoice RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE invoice RENAME COLUMN updated TO updated_at');

        $this->addSql('ALTER TABLE location RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE location RENAME COLUMN updated TO updated_at');

        $this->addSql('ALTER TABLE mail_package RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE mail_package RENAME COLUMN updated TO updated_at');

        $this->addSql('ALTER TABLE phone RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE phone RENAME COLUMN updated TO updated_at');

        $this->addSql('ALTER TABLE prospect RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE prospect RENAME COLUMN updated TO updated_at');

        $this->addSql('ALTER TABLE subscription RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE subscription RENAME COLUMN updated TO updated_at');

        $this->addSql('ALTER TABLE users RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE users RENAME COLUMN updated TO updated_at');

        $this->addSql('ALTER TABLE saved_query RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE saved_query RENAME COLUMN updated TO updated_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE address RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE address RENAME COLUMN updated_at TO updated');

        $this->addSql('ALTER TABLE business_unit RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE business_unit RENAME COLUMN updated_at TO updated');

        $this->addSql('ALTER TABLE company RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE company RENAME COLUMN updated_at TO updated');

        $this->addSql('ALTER TABLE customer RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE customer RENAME COLUMN updated_at TO updated');

        $this->addSql('ALTER TABLE email RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE email RENAME COLUMN updated_at TO updated');

        $this->addSql('ALTER TABLE invoice RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE invoice RENAME COLUMN updated_at TO updated');

        $this->addSql('ALTER TABLE location RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE location RENAME COLUMN updated_at TO updated');

        $this->addSql('ALTER TABLE mail_package RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE mail_package RENAME COLUMN updated_at TO updated');

        $this->addSql('ALTER TABLE phone RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE phone RENAME COLUMN updated_at TO updated');

        $this->addSql('ALTER TABLE prospect RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE prospect RENAME COLUMN updated_at TO updated');

        $this->addSql('ALTER TABLE subscription RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE subscription RENAME COLUMN updated_at TO updated');

        $this->addSql('ALTER TABLE users RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE users RENAME COLUMN updated_at TO updated');

        $this->addSql('ALTER TABLE saved_query RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE saved_query RENAME COLUMN updated_at TO updated');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241010191451 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscription ADD customer_id INT NOT NULL');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D39395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A3C664D39395C3F3 ON subscription (customer_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D39395C3F3');
        $this->addSql('DROP INDEX IDX_A3C664D39395C3F3');
        $this->addSql('ALTER TABLE subscription DROP customer_id');
    }
}

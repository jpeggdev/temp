<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241010174243 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscription ALTER frequency DROP NOT NULL');
        $this->addSql('ALTER TABLE subscription ALTER price DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscription ALTER frequency SET NOT NULL');
        $this->addSql('ALTER TABLE subscription ALTER price SET NOT NULL');
    }
}

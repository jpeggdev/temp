<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241213201210 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trade ADD long_name VARCHAR(255) default NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trade DROP long_name');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326182911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource_type ADD text_color VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE resource_type ADD border_color VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE resource_type ADD background_color VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource_type DROP background_color');
        $this->addSql('ALTER TABLE resource_type DROP text_color');
        $this->addSql('ALTER TABLE resource_type DROP border_color');
    }
}

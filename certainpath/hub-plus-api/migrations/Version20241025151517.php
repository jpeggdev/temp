<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241025151517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert ASI, ESI, PSI, and RSI into the trade table with specific descriptions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO trade (name, description) VALUES 
            ('ASI', 'HVAC Service Installer - Specializes in installation and servicing of HVAC systems and related mechanical installations'),
            ('ESI', 'Electrical Service Installer - Specializes in electrical systems installation and maintenance'),
            ('PSI', 'Plumbing Service Installer - Focused on installation and repair of plumbing systems'),
            ('RSI', 'Roofing Service Installer - Specializes in roofing installation and repair')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM trade WHERE name IN ('ASI', 'ESI', 'PSI', 'RSI')");
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250311155204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE campaign_product (
                id SERIAL NOT NULL,
                name TEXT NOT NULL,
                type TEXT DEFAULT \'service\' NOT NULL,
                description TEXT NOT NULL,
                category TEXT DEFAULT NULL,
                sub_category TEXT DEFAULT NULL,
                format TEXT DEFAULT NULL,
                prospect_price TEXT DEFAULT NULL,
                customer_price TEXT DEFAULT NULL,
                mailer_description TEXT NOT NULL,
                code TEXT NOT NULL,
                has_colored_stock BOOLEAN NOT NULL,
                brand TEXT DEFAULT NULL,
                size TEXT DEFAULT NULL,
                distribution_method TEXT NOT NULL,
                target_audience TEXT DEFAULT NULL,
                PRIMARY KEY(id)
            )'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7BF098815E237E06 ON campaign_product (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE campaign_product');
    }
}

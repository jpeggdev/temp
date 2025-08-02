<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250728161013 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE campaign_product ALTER prospect_price TYPE NUMERIC(12, 5)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE campaign_product ALTER customer_price TYPE NUMERIC(12, 5)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE campaign_product ALTER prospect_price TYPE NUMERIC(12, 2)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE campaign_product ALTER customer_price TYPE NUMERIC(12, 2)
        SQL);
    }
}

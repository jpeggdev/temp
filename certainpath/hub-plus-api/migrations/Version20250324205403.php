<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250324205403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE batch_postage (id SERIAL NOT NULL, reference TEXT NOT NULL, quantity_sent INT DEFAULT 0 NOT NULL, cost NUMERIC(12, 2) DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN batch_postage.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN batch_postage.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE campaign_product ALTER prospect_price TYPE NUMERIC(12, 2) USING prospect_price::numeric(12,2)');
        $this->addSql('ALTER TABLE campaign_product ALTER customer_price TYPE NUMERIC(12, 2) USING prospect_price::numeric(12,2)');
        $this->addSql('ALTER TABLE campaign_product ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE campaign_product ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('COMMENT ON COLUMN campaign_product.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN campaign_product.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER INDEX if exists idx_33d4f045591cc992 RENAME TO IDX_DC8C74C371F7E88B');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE batch_postage');
        $this->addSql('ALTER INDEX if exists idx_dc8c74c371f7e88b RENAME TO idx_33d4f045591cc992');
        $this->addSql('ALTER TABLE campaign_product ALTER prospect_price TYPE TEXT');
        $this->addSql('ALTER TABLE campaign_product ALTER customer_price TYPE TEXT');
        $this->addSql('ALTER TABLE campaign_product ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE campaign_product ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN campaign_product.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN campaign_product.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }
}

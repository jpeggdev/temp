<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250124155801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE IF EXISTS event_id_seq1 CASCADE');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS event_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql("ALTER TABLE event ALTER COLUMN id SET DEFAULT nextval('event_id_seq')");
        $this->addSql('CREATE TABLE IF NOT EXISTS company_trade (company_id INT NOT NULL, trade_id INT NOT NULL, PRIMARY KEY(company_id, trade_id))');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_39EBE103979B1AD6 ON company_trade (company_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_39EBE103C2D9760 ON company_trade (trade_id)');
        $this->addSql('CREATE TABLE IF NOT EXISTS report (id SERIAL NOT NULL, company_id INT NOT NULL, name TEXT NOT NULL, last_run TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_C42F7784979B1AD6 ON report (company_id)');
        $this->addSql('COMMENT ON COLUMN report.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN report.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE company_trade ADD CONSTRAINT FK_39EBE103979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE company_trade ADD CONSTRAINT FK_39EBE103C2D9760 FOREIGN KEY (trade_id) REFERENCES trade (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE IF EXISTS company_trade DROP CONSTRAINT FK_39EBE103979B1AD6');
        $this->addSql('ALTER TABLE IF EXISTS company_trade DROP CONSTRAINT FK_39EBE103C2D9760');
        $this->addSql('ALTER TABLE IF EXISTS report DROP CONSTRAINT FK_C42F7784979B1AD6');
        $this->addSql('DROP TABLE IF EXISTS company_trade');
        $this->addSql('DROP TABLE IF EXISTS report');
    }
}

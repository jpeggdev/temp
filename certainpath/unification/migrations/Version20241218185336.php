<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241218185336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE company_trade (company_id INT NOT NULL, trade_id INT NOT NULL, PRIMARY KEY(company_id, trade_id))');
        $this->addSql('CREATE INDEX IDX_39EBE103979B1AD6 ON company_trade (company_id)');
        $this->addSql('CREATE INDEX IDX_39EBE103C2D9760 ON company_trade (trade_id)');
        $this->addSql('ALTER TABLE company_trade ADD CONSTRAINT FK_39EBE103979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE company_trade ADD CONSTRAINT FK_39EBE103C2D9760 FOREIGN KEY (trade_id) REFERENCES trade (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company_trade DROP CONSTRAINT FK_39EBE103979B1AD6');
        $this->addSql('ALTER TABLE company_trade DROP CONSTRAINT FK_39EBE103C2D9760');
        $this->addSql('DROP TABLE company_trade');
    }
}

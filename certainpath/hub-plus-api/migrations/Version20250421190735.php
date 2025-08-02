<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250421190735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE batch_invoice (id SERIAL NOT NULL, account_identifier TEXT NOT NULL, batch_reference TEXT NOT NULL, invoice_reference TEXT NOT NULL, data JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE batch_postage ALTER cost SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE batch_invoice');
        $this->addSql('ALTER TABLE batch_postage ALTER cost DROP NOT NULL');
    }
}

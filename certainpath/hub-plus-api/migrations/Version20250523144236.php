<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250523144236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_discount_ledger DROP CONSTRAINT FK_67E8ECB3979B1AD6');
        $this->addSql('ALTER TABLE event_discount_ledger ADD CONSTRAINT FK_67E8ECB3979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_discount_ledger DROP CONSTRAINT fk_67e8ecb3979b1ad6');
        $this->addSql('ALTER TABLE event_discount_ledger ADD CONSTRAINT fk_67e8ecb3979b1ad6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

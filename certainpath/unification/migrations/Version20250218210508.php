<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218210508 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS restricted_address_id_seq');
        $this->addSql('SELECT setval(\'restricted_address_id_seq\', (SELECT MAX(id) FROM restricted_address))');
        $this->addSql('ALTER TABLE restricted_address ALTER id SET DEFAULT nextval(\'restricted_address_id_seq\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE restricted_address ALTER id DROP DEFAULT');
    }
}

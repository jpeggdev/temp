<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241019190538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SELECT setval(\'field_service_software_id_seq\', (SELECT MAX(id) FROM field_service_software))');
        $this->addSql('ALTER TABLE field_service_software ALTER id SET DEFAULT nextval(\'field_service_software_id_seq\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE field_service_software ALTER id DROP DEFAULT');
    }
}

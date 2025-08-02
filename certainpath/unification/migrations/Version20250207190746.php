<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250207190746 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM tag');
        $this->addSql('DROP INDEX tag_company_tag_name_idx');
        $this->addSql('CREATE UNIQUE INDEX tag_company_tag_name_uniq ON tag (company_id, name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX tag_company_tag_name_uniq');
        $this->addSql('CREATE INDEX tag_company_tag_name_idx ON tag (company_id, name)');
    }
}

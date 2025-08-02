<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522192720 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_4fbf094fa063de11
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_4fbf094f9c0c3b6d
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_4fbf094fa063de11 ON company (company_email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_4fbf094f9c0c3b6d ON company (website_url)
        SQL);
    }
}

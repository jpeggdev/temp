<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527121819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the app_setting table and inserts a legacy banner toggle setting.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE app_setting (
                id SERIAL NOT NULL,
                name VARCHAR(255) NOT NULL,
                value VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_722938D55E237E06 ON app_setting (name)
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO app_setting (name, value)
            VALUES ('legacyBannerToggle', '1')
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE app_setting
        SQL);
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250701231509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE filesystem_node ADD created_by_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE filesystem_node ADD CONSTRAINT FK_4E10707DB03A8386 FOREIGN KEY (created_by_id) REFERENCES employee (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4E10707DB03A8386 ON filesystem_node (created_by_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE filesystem_node DROP CONSTRAINT FK_4E10707DB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4E10707DB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE filesystem_node DROP created_by_id
        SQL);
    }
}

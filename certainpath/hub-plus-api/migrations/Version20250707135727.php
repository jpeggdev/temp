<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707135727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE filesystem_node ADD updated_by_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE filesystem_node ADD CONSTRAINT FK_4E10707D896DBBDE FOREIGN KEY (updated_by_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4E10707D896DBBDE ON filesystem_node (updated_by_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE filesystem_node DROP CONSTRAINT FK_4E10707D896DBBDE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4E10707D896DBBDE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE filesystem_node DROP updated_by_id
        SQL);
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250108004842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_169E6FB9BFB7ED9E');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_169E6FB9BFB7ED9E ON course (course_code) WHERE (deleted_at IS NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_169e6fb9bfb7ed9e');
        $this->addSql('CREATE UNIQUE INDEX uniq_169e6fb9bfb7ed9e ON course (course_code)');
    }
}

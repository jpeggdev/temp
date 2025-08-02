<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250116221513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE prospect_tag (prospect_id INT NOT NULL, tag_id INT NOT NULL, PRIMARY KEY(prospect_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_8859B408D182060A ON prospect_tag (prospect_id)');
        $this->addSql('CREATE INDEX IDX_8859B408BAD26311 ON prospect_tag (tag_id)');
        $this->addSql('CREATE TABLE tag (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN tag.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN tag.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE prospect_tag ADD CONSTRAINT FK_8859B408D182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE prospect_tag ADD CONSTRAINT FK_8859B408BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE IF EXISTS prospect_tag DROP CONSTRAINT FK_8859B408D182060A');
        $this->addSql('ALTER TABLE IF EXISTS prospect_tag DROP CONSTRAINT FK_8859B408BAD26311');
        $this->addSql('DROP TABLE IF EXISTS prospect_tag');
        $this->addSql('DROP TABLE IF EXISTS tag');
    }
}

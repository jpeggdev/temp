<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241019182652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company ADD company_email VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FBF094FA063DE11 ON company (company_email)');
        $this->addSql('ALTER TABLE employee ADD work_email VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5D9F75A1CF69075B ON employee (work_email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_4FBF094FA063DE11');
        $this->addSql('ALTER TABLE company DROP company_email');
        $this->addSql('DROP INDEX UNIQ_5D9F75A1CF69075B');
        $this->addSql('ALTER TABLE employee DROP work_email');
    }
}

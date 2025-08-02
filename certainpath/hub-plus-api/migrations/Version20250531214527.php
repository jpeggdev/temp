<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250531214527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE payment_profile_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE payment_profile (id INT NOT NULL, employee_id INT NOT NULL, authnet_customer_id VARCHAR(255) NOT NULL, authnet_payment_profile_id VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, uuid UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_981EA4E58C03F15C ON payment_profile (employee_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_981EA4E5D17F50A6 ON payment_profile (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN payment_profile.created_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN payment_profile.updated_at IS '(DC2Type:datetimetz_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_profile ADD CONSTRAINT FK_981EA4E58C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP SEQUENCE payment_profile_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_profile DROP CONSTRAINT FK_981EA4E58C03F15C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payment_profile
        SQL);
    }
}

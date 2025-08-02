<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250330220018 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE email_template_variable (
                id SERIAL NOT NULL,
                company_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                value TEXT NOT NULL,
                description TEXT DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id))
            '
        );

        $this->addSql('
            CREATE INDEX IDX_C610A839979B1AD6
            ON email_template_variable (company_id)
        ');

        $this->addSql('
            ALTER TABLE email_template_variable
            ADD CONSTRAINT FK_C610A839979B1AD6
            FOREIGN KEY (company_id)
            REFERENCES company (id)
            NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE email_template_variable
            DROP CONSTRAINT FK_C610A839979B1AD6
        ');

        $this->addSql('DROP TABLE email_template_variable');
    }
}

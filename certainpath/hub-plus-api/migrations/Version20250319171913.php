<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Color;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250319171913 extends AbstractMigration
{
    private array $colors = [
        Color::COLOR_BLUE,
        Color::COLOR_RED,
        Color::COLOR_GREEN,
        Color::COLOR_ORANGE,
        Color::COLOR_PURPLE,
        Color::COLOR_PINK,
        Color::COLOR_GRAY,
    ];

    public function up(Schema $schema): void
    {
        $this->createEmailTemplateTable();
        $this->createColorTable();
        $this->createEmailTemplateCategoryTable();
        $this->createEmailTemplateEmailTemplateCategoryMappingTable();

        $this->insertInitialData();
    }

    public function down(Schema $schema): void
    {
        $this->dropEmailTemplateEmailTemplateCategoryMappingTable();
        $this->dropEmailTemplateTable();
        $this->dropEmailTemplateCategoryTable();
        $this->dropColorTable();
    }

    private function createEmailTemplateTable(): void
    {
        $this->addSql('
            CREATE TABLE email_template (
                id SERIAL NOT NULL,
                name VARCHAR(255) NOT NULL,
                email_subject VARCHAR(255) NOT NULL,
                email_content TEXT NOT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        ');
    }

    private function createEmailTemplateCategoryTable(): void
    {
        $this->addSql('
            CREATE TABLE email_template_category (
                id SERIAL NOT NULL,
                name VARCHAR(255) NOT NULL,
                displayed_name VARCHAR(255) NOT NULL,
                description TEXT DEFAULT NULL,
                color_id INT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('
            CREATE UNIQUE INDEX unique_email_template_category_name
            ON email_template_category (name)
        ');

        $this->addSql('
            CREATE INDEX IDX_C1136757ADA1FB5
            ON email_template_category (color_id)
        ');

        $this->addSql('
            ALTER TABLE email_template_category
            ADD CONSTRAINT FK_C1136757ADA1FB5
            FOREIGN KEY (color_id)
            REFERENCES color (id)
            NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    private function createEmailTemplateEmailTemplateCategoryMappingTable(): void
    {
        $this->addSql('
            CREATE TABLE email_template_email_template_category_mapping (
                id SERIAL NOT NULL,
                email_template_id INT NOT NULL,
                email_template_category_id INT NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('
            CREATE INDEX IDX_34C1C80131A730F
            ON email_template_email_template_category_mapping (email_template_id)
        ');
        $this->addSql('
            CREATE INDEX IDX_34C1C80D7D7DE00
            ON email_template_email_template_category_mapping (email_template_category_id)
        ');

        $this->addSql('
            ALTER TABLE email_template_email_template_category_mapping
            ADD CONSTRAINT FK_34C1C80131A730F
            FOREIGN KEY (email_template_id)
            REFERENCES email_template (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');

        $this->addSql('
            ALTER TABLE email_template_email_template_category_mapping
            ADD CONSTRAINT FK_34C1C80D7D7DE00
            FOREIGN KEY (email_template_category_id)
            REFERENCES email_template_category (id)
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    private function createColorTable(): void
    {
        $this->addSql('
            CREATE TABLE color (
                id SERIAL NOT NULL,
                value VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('
            CREATE UNIQUE INDEX unique_color_value
            ON color (value)
        ');
    }

    private function insertInitialData(): void
    {
        foreach ($this->colors as $color) {
            $this->addSql(sprintf("
                INSERT INTO color (value)
                VALUES ('%s')",
                $color
            ));
        }
    }

    private function dropEmailTemplateTable(): void
    {
        $this->addSql('DROP TABLE email_template');
    }

    private function dropEmailTemplateCategoryTable(): void
    {
        $this->addSql('
            ALTER TABLE email_template_category
            DROP CONSTRAINT FK_C1136757ADA1FB5
        ');
        $this->addSql('DROP INDEX unique_email_template_category_name');
        $this->addSql('DROP INDEX IDX_C1136757ADA1FB5');
        $this->addSql('DROP TABLE email_template_category');
    }

    private function dropEmailTemplateEmailTemplateCategoryMappingTable(): void
    {
        $this->addSql('
            ALTER TABLE email_template_email_template_category_mapping
            DROP CONSTRAINT FK_34C1C80131A730F
        ');
        $this->addSql('
            ALTER TABLE email_template_email_template_category_mapping
            DROP CONSTRAINT FK_34C1C80D7D7DE00
        ');
        $this->addSql('DROP INDEX IDX_34C1C80131A730F');
        $this->addSql('DROP INDEX IDX_34C1C80D7D7DE00');
        $this->addSql('DROP TABLE email_template_email_template_category_mapping');
    }

    private function dropColorTable(): void
    {
        $this->addSql('DROP INDEX unique_color_value');
        $this->addSql('DROP TABLE color');
    }
}

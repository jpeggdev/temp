<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250326183556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sets background_color, text_color, and border_color for Document, Video, and Podcast resource types.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            UPDATE resource_type
               SET background_color = 'rgb(250 245 255 / 1)',
                   text_color = 'rgb(126 34 206 / 1)',
                   border_color = 'rgb(233 213 255 / 1)'
             WHERE name = 'Document'
        ");

        $this->addSql("
            UPDATE resource_type
               SET background_color = 'rgb(239 246 255 / 1)',
                   text_color = 'rgb(29 78 216 / 1)',
                   border_color = 'rgb(191 219 254 / 1)'
             WHERE name = 'Video'
        ");

        $this->addSql("
            UPDATE resource_type
               SET background_color = 'rgb(255 251 235 / 1)',
                   text_color = 'rgb(180 83 9 / 1)',
                   border_color = 'rgb(253 230 138 / 1)'
             WHERE name = 'Podcast'
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            UPDATE resource_type
               SET background_color = NULL,
                   text_color = NULL,
                   border_color = NULL
             WHERE name IN ('Document','Video','Podcast')
        ");
    }
}

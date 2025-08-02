<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use App\Module\Hub\Feature\FileManagement\Util\FileTypeClassifier;

/**
 * Add file_type column to filesystem_node table
 */
final class Version20250703123753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add file_type column to filesystem_node table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE filesystem_node ADD COLUMN IF NOT EXISTS file_type VARCHAR(255) NULL');

        $this->addSql("UPDATE filesystem_node SET file_type = 'folder' WHERE type = 'folder'");

        $this->addSql("UPDATE filesystem_node SET file_type = 'image' WHERE type = 'file' AND mime_type LIKE 'image/%'");

        $this->addSql("UPDATE filesystem_node SET file_type = 'video' WHERE type = 'file' AND mime_type LIKE 'video/%'");

        $this->addSql("UPDATE filesystem_node SET file_type = 'audio' WHERE type = 'file' AND mime_type LIKE 'audio/%'");

        $this->addSql("UPDATE filesystem_node SET file_type = 'pdf' WHERE type = 'file' AND mime_type = 'application/pdf'");

        $this->addSql("UPDATE filesystem_node SET file_type = 'document' WHERE type = 'file' AND (
            mime_type = 'application/msword' OR
            mime_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' OR
            mime_type = 'application/vnd.oasis.opendocument.text' OR
            mime_type = 'text/plain' OR
            mime_type = 'text/rtf' OR
            mime_type = 'text/html' OR
            mime_type = 'text/markdown'
        )");

        $this->addSql("UPDATE filesystem_node SET file_type = 'spreadsheet' WHERE type = 'file' AND (
            mime_type = 'application/vnd.ms-excel' OR
            mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' OR
            mime_type = 'application/vnd.oasis.opendocument.spreadsheet' OR
            mime_type = 'text/csv'
        )");

        $this->addSql("UPDATE filesystem_node SET file_type = 'presentation' WHERE type = 'file' AND (
            mime_type = 'application/vnd.ms-powerpoint' OR
            mime_type = 'application/vnd.openxmlformats-officedocument.presentationml.presentation' OR
            mime_type = 'application/vnd.oasis.opendocument.presentation'
        )");

        $this->addSql("UPDATE filesystem_node SET file_type = 'archive' WHERE type = 'file' AND (
            mime_type = 'application/zip' OR
            mime_type = 'application/x-rar-compressed' OR
            mime_type = 'application/x-7z-compressed' OR
            mime_type = 'application/x-tar' OR
            mime_type = 'application/gzip' OR
            mime_type = 'application/x-bzip2'
        )");

        $this->addSql("UPDATE filesystem_node SET file_type = 'other' WHERE file_type IS NULL");
        $this->addSql('ALTER TABLE filesystem_node ALTER COLUMN file_type SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE filesystem_node DROP COLUMN IF EXISTS file_type');
    }
}

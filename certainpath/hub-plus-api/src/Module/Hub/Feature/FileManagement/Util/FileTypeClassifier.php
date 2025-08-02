<?php

namespace App\Module\Hub\Feature\FileManagement\Util;

class FileTypeClassifier
{
    public const string TYPE_IMAGE = 'image';
    public const string TYPE_PDF = 'pdf';
    public const string TYPE_DOCUMENT = 'document';
    public const string TYPE_SPREADSHEET = 'spreadsheet';
    public const string TYPE_PRESENTATION = 'presentation';
    public const string TYPE_VIDEO = 'video';
    public const string TYPE_AUDIO = 'audio';
    public const string TYPE_ARCHIVE = 'archive';
    public const string TYPE_OTHER = 'other';
    public const string TYPE_FOLDER = 'folder';

    /**
     * Classify a file based on its MIME type.
     */
    public static function classifyByMimeType(?string $mimeType): string
    {
        if (!$mimeType) {
            return self::TYPE_OTHER;
        }

        if (str_starts_with($mimeType, 'image/')) {
            return self::TYPE_IMAGE;
        }

        if (str_starts_with($mimeType, 'video/')) {
            return self::TYPE_VIDEO;
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return self::TYPE_AUDIO;
        }

        if ('application/pdf' === $mimeType) {
            return self::TYPE_PDF;
        }

        if (self::isDocument($mimeType)) {
            return self::TYPE_DOCUMENT;
        }

        if (self::isSpreadsheet($mimeType)) {
            return self::TYPE_SPREADSHEET;
        }

        if (self::isPresentation($mimeType)) {
            return self::TYPE_PRESENTATION;
        }

        if (self::isArchive($mimeType)) {
            return self::TYPE_ARCHIVE;
        }

        return self::TYPE_OTHER;
    }

    private static function isDocument(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.oasis.opendocument.text',
            'text/plain',
            'text/rtf',
            'text/html',
            'text/markdown',
        ]);
    }

    private static function isSpreadsheet(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.oasis.opendocument.spreadsheet',
            'text/csv',
        ]);
    }

    private static function isPresentation(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.oasis.opendocument.presentation',
        ]);
    }

    private static function isArchive(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/zip',
            'application/x-rar-compressed',
            'application/x-7z-compressed',
            'application/x-tar',
            'application/gzip',
            'application/x-bzip2',
        ]);
    }
}

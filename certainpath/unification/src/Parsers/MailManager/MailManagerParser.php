<?php

namespace App\Parsers\MailManager;

use App\Parsers\AbstractParser;

use function App\Functions\app_lower;

abstract class MailManagerParser extends AbstractParser
{
    public function getSourceId(): string
    {
        return 'MLMGR';
    }

    public static function getSourceName(): string
    {
        return 'mailmanager';
    }

    public static function hasMatchingFileName(string $fileName): bool
    {
        return match (static::class) {
            ProspectParser::class => (str_contains(app_lower($fileName), '.dbf')),
            default => false,
        };
    }
}

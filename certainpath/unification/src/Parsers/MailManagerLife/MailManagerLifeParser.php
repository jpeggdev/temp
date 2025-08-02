<?php

namespace App\Parsers\MailManagerLife;

use App\Parsers\AbstractParser;

abstract class MailManagerLifeParser extends AbstractParser
{
    public function getSourceId(): string
    {
        return 'MMLIFE';
    }

    public static function getSourceName(): string
    {
        return 'mailmanagerlife';
    }

    public static function hasMatchingFileName(string $fileName): bool
    {
        return match (static::class) {
            default => false,
        };
    }
}

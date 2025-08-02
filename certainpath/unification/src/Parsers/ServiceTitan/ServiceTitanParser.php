<?php

namespace App\Parsers\ServiceTitan;

use App\Parsers\AbstractParser;

abstract class ServiceTitanParser extends AbstractParser
{
    public function getSourceId(): string
    {
        return 'SVT';
    }

    public static function getSourceName(): string
    {
        return 'servicetitan';
    }

    public static function hasMatchingFileName(string $fileName): bool
    {
        return match (static::class) {
            default => false,
        };
    }
}

<?php

namespace App\Parsers\GenericIngest;

use App\Parsers\AbstractParser;

abstract class GenericIngestParser extends AbstractParser
{
    public function getSourceId(): string
    {
        return 'GENIN';
    }

    public static function getSourceName(): string
    {
        return 'generic_ingest';
    }

    public static function hasMatchingFileName(string $fileName): bool
    {
        return match (static::class) {
            default => false,
        };
    }
}

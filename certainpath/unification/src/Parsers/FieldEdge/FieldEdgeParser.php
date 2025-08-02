<?php

namespace App\Parsers\FieldEdge;

use App\Parsers\AbstractParser;

abstract class FieldEdgeParser extends AbstractParser
{
    public function getSourceId(): string
    {
        return 'FE';
    }

    public static function getSourceName(): string
    {
        return 'fieldedge';
    }

    public static function hasMatchingFileName(string $fileName): bool
    {
        return match (static::class) {
            default => false,
        };
    }
}

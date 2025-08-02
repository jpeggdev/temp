<?php

namespace App\Parsers\Successware;

use App\Parsers\AbstractParser;

abstract class SuccesswareParser extends AbstractParser
{
    public function getSourceId(): string
    {
        return 'SUW';
    }

    public static function getSourceName(): string
    {
        return 'successware';
    }

    public static function hasMatchingFileName(string $fileName): bool
    {
        return match (static::class) {
            default => false,
        };
    }
}

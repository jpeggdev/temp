<?php

namespace App\ValueObjects\AddressDetection;

class CountyRoadName
{
    private const COUNTY_ROAD_PATTERNS = [
        '/\bCO\.\s*RD\b/i',
        '/\bCO\.\s*ROAD\b/i',
        '/\bCO\s*RD\b/i',
        '/\bCO\s*ROAD\b/i',
        '/\bCOUNTY\s*RD\b/i',
        '/\bCOUNTY\s*ROAD\b/i',
        '/\bCR\s*\d+/i',
        '/\bCO\.\s*ROAD\s*\d+/i',
        '/\bCO\.\s*RD\.\s*\d+/i',
        '/\bCO\s*RD\s*\d+/i',
        '/\bCO\s*ROAD\s*\d+/i',
    ];

    private function __construct(
        private readonly string $searchText,
    ) {
    }

    public static function fromText(
        string $searchText
    ): self {
        return new self($searchText);
    }

    public function matches(): bool
    {
        foreach (self::COUNTY_ROAD_PATTERNS as $pattern) {
            if (preg_match($pattern, $this->searchText)) {
                return true;
            }
        }
        return false;
    }
}

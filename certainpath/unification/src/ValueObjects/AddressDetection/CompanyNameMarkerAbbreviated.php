<?php

namespace App\ValueObjects\AddressDetection;

class CompanyNameMarkerAbbreviated
{
    private const MARKERS = [
        '/\b\w+\s+CO\b/i',           // "Umbrella Co"
        '/\b\w+\s*&\s*CO\b/i',       // "Smith & Co"
        '/\bCO\s*\./i',              // "Co." (but not followed by RD)
        '/\bCO\s*,/i',               // "Co,"
        '/\bCO\s*$/i',               // "Co" at end of string
    ];

    private function __construct(
        private readonly string $searchText
    ) {
    }

    public static function fromText(string $searchText): self
    {
        return new self($searchText);
    }

    public function matches(): bool
    {
        foreach (self::MARKERS as $pattern) {
            if (preg_match($pattern, $this->searchText)) {
                return true;
            }
        }
        return false;
    }
}

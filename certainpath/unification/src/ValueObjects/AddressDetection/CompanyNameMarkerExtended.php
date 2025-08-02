<?php

namespace App\ValueObjects\AddressDetection;

class CompanyNameMarkerExtended
{
    private const MARKERS = [
        '/\b\w+\s+COMPANY\s*$/i',         // "ABC COMPANY" (end of string)
        '/\b\w+\s+COMPANY\s*,/i',         // "ABC COMPANY," (followed by comma)
        '/\b\w+\s+COMPANY\s*\./i',        // "ABC COMPANY." (followed by period)
        '/\bCOMPANY\s*$/i',               // "COMPANY" at end of string
        '/\bCOMPANY\s*,/i',               // "COMPANY," (followed by comma)
        '/\bCOMPANY\s*\./i',              // "COMPANY." (followed by period)
        '/\b\w+\s+COMPANY\s+\w+/i',       // "ABC COMPANY INC" (followed by another word that's not a street type)
    ];

    private function __construct(
        private readonly string $searchText
    ) {
    }

    public static function fromText(
        string $searchText
    ): self {
        return new self($searchText);
    }

    public function matches(): bool
    {
        foreach (self::MARKERS as $pattern) {
            if (preg_match($pattern, $this->searchText)) {
                // Additional check: make sure it's not followed by a street type
                if (!preg_match('/\bCOMPANY\s+(?:RD|ROAD|ST|STREET|AVE|AVENUE|DR|DRIVE|LN|LANE|BLVD|BOULEVARD|WAY|CT|COURT|PL|PLACE|HOLLOW)\b/i', $this->searchText)) {
                    return true; // Match as business indicator
                }
            }
        }
        return false;
    }
}

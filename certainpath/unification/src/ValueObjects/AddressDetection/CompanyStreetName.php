<?php

namespace App\ValueObjects\AddressDetection;

class CompanyStreetName
{
    private const STREET_NAME_PATTERNS = [
        '/\bCOMPANY\s+RD\b/i',
        '/\bCOMPANY\s+ROAD\b/i',
        '/\bCOMPANY\s+ST\b/i',
        '/\bCOMPANY\s+STREET\b/i',
        '/\bCOMPANY\s+AVE\b/i',
        '/\bCOMPANY\s+AVENUE\b/i',
        '/\bCOMPANY\s+DR\b/i',
        '/\bCOMPANY\s+DRIVE\b/i',
        '/\bCOMPANY\s+LN\b/i',
        '/\bCOMPANY\s+LANE\b/i',
        '/\bCOMPANY\s+BLVD\b/i',
        '/\bCOMPANY\s+BOULEVARD\b/i',
        '/\bCOMPANY\s+WAY\b/i',
        '/\bCOMPANY\s+CT\b/i',
        '/\bCOMPANY\s+COURT\b/i',
        '/\bCOMPANY\s+PL\b/i',
        '/\bCOMPANY\s+PLACE\b/i',
        '/\w+\s+COMPANY\s+RD\b/i',
        '/\w+\s+COMPANY\s+ROAD\b/i',
        '/\w+\s+COMPANY\s+ST\b/i',
        '/\w+\s+COMPANY\s+STREET\b/i',
        '/\w+\s+COMPANY\s+AVE\b/i',
        '/\w+\s+COMPANY\s+AVENUE\b/i',
        '/\w+\s+COMPANY\s+DR\b/i',
        '/\w+\s+COMPANY\s+DRIVE\b/i',
        '/\w+\s+COMPANY\s+LN\b/i',
        '/\w+\s+COMPANY\s+LANE\b/i',
        '/\w+\s+COMPANY\s+BLVD\b/i',
        '/\w+\s+COMPANY\s+BOULEVARD\b/i',
        '/\w+\s+COMPANY\s+WAY\b/i',
        '/\w+\s+COMPANY\s+CT\b/i',
        '/\w+\s+COMPANY\s+COURT\b/i',
        '/\w+\s+COMPANY\s+PL\b/i',
        '/\w+\s+COMPANY\s+PLACE\b/i',
        '/\w+\s+COMPANY\s+HOLLOW\b/i',
    ];

    private function __construct(
        private readonly string $searchText,
    ) {
    }


    public static function fromText(string $searchText): self
    {
        return new self($searchText);
    }

    public function matches(): bool
    {
        foreach (self::STREET_NAME_PATTERNS as $pattern) {
            if (preg_match($pattern, $this->searchText)) {
                return true;
            }
        }
        return false;
    }
}

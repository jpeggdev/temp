<?php

namespace App\ValueObjects\AddressDetection;

class StrongBusinessIndicators
{
    private const STRONG_BUSINESS_INDICATORS = [
        'PO_BOX_PATTERNS' => [
            'PO BOX',
            'P.O. BOX',
            'P O BOX',
            'POST OFFICE BOX',
        ],
        'COMMERCIAL_KEYWORDS' => [
            'SUITE',
            'STE', //Risky, mitigated
            'UNIT',
            '#',
            'DEPT',
            'DEPARTMENT',
            'FLOOR',
            'FL', //Risky, mitigated
        ],
        'BUSINESS_SUFFIXES' => [
            'INC', //Risky, mitigated
            'LLC',
            'LTD',
            'CORP',
            'CO', //Risky, mitigated
            'COMPANY', //Risky, mitigated
            'ASSOCIATES',
            'CORPORATION',
            'LIMITED',
            'INCORPORATED',
        ],
        'COMMERCIAL_ZONES' => [
            'INDUSTRIAL PARK',
            'BUSINESS PARK',
            'BUSINESS CENTER',
            'PLAZA',
            'MALL', //Risky, mitigated
            'SHOPPING CENTER',
            'OFFICE PARK',
        ],
    ];
    private function __construct(
        private readonly string $searchText,
    ) {
    }

    public static function fromText(
        string $searchText,
    ): self {
        return new self($searchText);
    }

    public function has(): bool
    {
        foreach (self::STRONG_BUSINESS_INDICATORS as $categoryName => $indicators) {
            foreach ($indicators as $indicator) {
                if ($this->matchesIndicator($this->searchText, $indicator, $categoryName)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function matchesIndicator(string $searchText, string $indicator, string $category): bool
    {
        // For commercial keywords like STE, SUITE, etc., we need word boundaries
        if (in_array($category, ['COMMERCIAL_KEYWORDS', 'BUSINESS_SUFFIXES', 'COMMERCIAL_ZONES'])) {
            return $this->matchesWithWordBoundary($searchText, $indicator);
        }

        // For other categories, use simple string contains (existing behavior)
        return str_contains($searchText, $indicator);
    }

    private function matchesWithWordBoundary(string $searchText, string $indicator): bool
    {
        // Special handling for "CO" - check for county road context
        if (strtoupper($indicator) === 'CO') {
            return $this->matchesCompanyNameAbbreviatedIndicator($searchText);
        }

        // Special handling for "COMPANY" - check for street name context
        if (strtoupper($indicator) === 'COMPANY') {
            return $this->matchesCompanyNameExtendedIndicator($searchText);
        }


        // Create pattern with word boundaries for other indicators
        $pattern = '/\b' . preg_quote($indicator, '/') . '\b/i';

        return preg_match($pattern, $searchText) === 1;
    }

    private function matchesCompanyNameExtendedIndicator(string $searchText): bool
    {
        if (CompanyStreetName::fromText($searchText)->matches()) {
            return false;
        }

        return CompanyNameMarkerExtended::fromText($searchText)->matches();
    }

    private function matchesCompanyNameAbbreviatedIndicator(string $searchText): bool
    {
        if (CountyRoadName::fromText($searchText)->matches()) {
            return false;
        }

        return CompanyNameMarkerAbbreviated::fromText($searchText)->matches();
    }
}

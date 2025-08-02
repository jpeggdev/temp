<?php

namespace App\ValueObjects\AddressDetection;

class ModerateBusinessIndicators
{
    private const MODERATE_BUSINESS_INDICATORS = [
        'COMMERCIAL_STREET_TERMS' => [
            'COMMERCE',
            'INDUSTRIAL',
            'BUSINESS',
            'CORPORATE',
            'TECHNOLOGY',
            'ENTERPRISE',
        ],
        'STREET_TYPES' => [
            'ST',
            'STREET',
            'AVE',
            'AVENUE',
            'BLVD',
            'BOULEVARD',
            'DR',
            'DRIVE',
            'WAY',
            'RD',
            'ROAD',
            'COURT',
            'LANE',
            'LN',
            'HOLLOW',
        ],
    ];

    private function __construct(
        private readonly string $searchText
    ) {
    }

    public static function fromText(string $searchText): self
    {
        return new self($searchText);
    }

    public function has(): bool
    {
        foreach ($this->getModerateIndicators('COMMERCIAL_STREET_TERMS') as $commercialTerm) {
            foreach ($this->getModerateIndicators('STREET_TYPES') as $streetType) {
                $pattern = $commercialTerm . ' ' . $streetType;
                if (str_contains($this->searchText, $pattern)) {
                    return true;
                }
            }
        }

        // Also check for individual commercial street terms followed by street types
        // This handles cases where there might be extra spaces or variations
        foreach ($this->getModerateIndicators('COMMERCIAL_STREET_TERMS') as $commercialTerm) {
            if (str_contains($this->searchText, $commercialTerm)) {
                // If we find a commercial term, check if it's followed by a street type
                foreach ($this->getModerateIndicators('STREET_TYPES') as $streetType) {
                    if (str_contains($this->searchText, $streetType)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getModerateIndicators(?string $index = null): array
    {
        return $index ? self::MODERATE_BUSINESS_INDICATORS[$index] : self::MODERATE_BUSINESS_INDICATORS;
    }
}

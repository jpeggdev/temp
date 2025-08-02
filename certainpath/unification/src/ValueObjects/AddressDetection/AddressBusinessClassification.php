<?php

namespace App\ValueObjects\AddressDetection;

class AddressBusinessClassification
{
    private BusinessIndicators $businessIndicators;
    private AddressTextProcessor $textProcessor;

    public function __construct(
        ?BusinessIndicators $businessIndicators = null,
        ?AddressTextProcessor $textProcessor = null
    ) {
        $this->businessIndicators = $businessIndicators ?? new BusinessIndicators();
        $this->textProcessor = $textProcessor ?? new AddressTextProcessor();
    }

    public function classifyAddress(
        string $address1,
        string $address2 = '',
        string $city = ''
    ): BusinessClassificationResult {
        $searchText = $this->textProcessor->prepareSearchText($address1, $address2, $city);

        $hasStrongIndicators = $this->businessIndicators->hasStrongBusinessIndicators($searchText);
        $hasModerateIndicators = $this->businessIndicators->hasModerateBusinessIndicators($searchText);

        $isBusiness = $hasStrongIndicators || $hasModerateIndicators;
        $confidenceScore = $this->calculateConfidenceScore($hasStrongIndicators, $hasModerateIndicators);

        return new BusinessClassificationResult(
            isBusiness: $isBusiness,
            confidenceScore: $confidenceScore,
            hasStrongIndicators: $hasStrongIndicators,
            hasModerateIndicators: $hasModerateIndicators,
            searchText: $searchText
        );
    }

    private function calculateConfidenceScore(bool $hasStrongIndicators, bool $hasModerateIndicators): float
    {
        if ($hasStrongIndicators) {
            return 0.9; // High confidence for strong indicators
        }

        if ($hasModerateIndicators) {
            return 0.6; // Medium confidence for moderate indicators
        }

        return 0.1; // Low confidence - default to residential
    }
}

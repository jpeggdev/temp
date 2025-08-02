<?php

namespace App\ValueObjects\AddressDetection;

readonly class BusinessClassificationResult
{
    public function __construct(
        public bool $isBusiness,
        public float $confidenceScore,
        public bool $hasStrongIndicators,
        public bool $hasModerateIndicators,
        public string $searchText
    ) {
    }

    public function isResidential(): bool
    {
        return !$this->isBusiness;
    }

    public function hasHighConfidence(): bool
    {
        return $this->confidenceScore >= 0.8;
    }

    public function hasMediumConfidence(): bool
    {
        return $this->confidenceScore >= 0.5 && $this->confidenceScore < 0.8;
    }

    public function hasLowConfidence(): bool
    {
        return $this->confidenceScore < 0.5;
    }
}
<?php

namespace App\ValueObjects\AddressDetection;

class BusinessIndicators
{
    public function hasStrongBusinessIndicators(string $searchText): bool
    {
        return StrongBusinessIndicators::fromText($searchText)->has();
    }

    public function hasModerateBusinessIndicators(string $searchText): bool
    {
        return ModerateBusinessIndicators::fromText($searchText)->has();
    }
}

<?php

namespace App\ValueObjects\AddressDetection;

class AddressTextProcessor
{
    public function normalizeForMatching(string ...$addressParts): string
    {
        $filteredParts = array_filter($addressParts, fn($part) => !empty(trim($part)));
        $concatenated = implode(' ', $filteredParts);

        return strtoupper(trim($concatenated));
    }

    public function prepareSearchText(string $address1, string $address2 = '', string $city = ''): string
    {
        return $this->normalizeForMatching($address1, $address2, $city);
    }
}

<?php

namespace App\Services;

use App\Repository\TradeRepository;
use Doctrine\Common\Collections\ArrayCollection;

readonly class TradeService
{
    public function __construct(
        private TradeRepository $tradeRepository,
    ) {
    }

    /**
     * Sorts and capitalizes trade names.
     */
    public function getTradeNamesFormatted(): ArrayCollection
    {
        $tradeNames = $this->tradeRepository->fetchAllTradeNames();

        $formattedNames = $tradeNames->map(static function ($name) {
            $name = ucwords(strtolower($name));
            return str_replace('Hvac', 'HVAC', $name);
        });

        $formattedNamesArray = $formattedNames->toArray();
        uasort($formattedNamesArray, static function ($a, $b) {
            return strcmp($a, $b);
        });

        return new ArrayCollection(array_values($formattedNamesArray));
    }
}

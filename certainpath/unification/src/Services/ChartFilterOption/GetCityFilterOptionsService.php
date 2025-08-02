<?php

namespace App\Services\ChartFilterOption;

use App\DTO\Query\FilterOption\GetCityFilterOptionsDTO;
use App\DTO\Response\CityFilterOptionsResponseDTO;
use App\Repository\AddressRepository;

readonly class GetCityFilterOptionsService
{
    public function __construct(
        private AddressRepository $addressRepository
    ) {
    }

    public function getFilterOptions(GetCityFilterOptionsDTO $queryDTO): array
    {
        $result = $this->addressRepository->fetchDistinctCities(
            $queryDTO->page,
            $queryDTO->pageSize,
            $queryDTO->sortBy,
            $queryDTO->sortOrder,
            $queryDTO->intacctId,
            $queryDTO->searchTerm
        )
            ->map(fn($item) => ucwords(strtolower($item['city'])))
            ->toArray();

        $responseDTOs = [];
        $startIndex = ($queryDTO->page - 1) * $queryDTO->pageSize;

        foreach (array_values($result) as $index => $city) {
            $syntheticId = $startIndex + $index + 1;
            $responseDTOs[] = new CityFilterOptionsResponseDTO($syntheticId, $city);
        }

        return $responseDTOs;
    }
}

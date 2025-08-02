<?php

namespace App\Services\ChartFilterOption;

use App\DTO\Query\FilterOption\GetTradeFilterOptionsDTO;
use App\DTO\Response\CityFilterOptionsResponseDTO;
use App\DTO\Response\TradeFilterOptionsResponseDTO;
use App\Repository\TradeRepository;

readonly class GetTradeFilterOptionsService
{
    public function __construct(
        private TradeRepository $tradeRepository
    ) {
    }

    public function getFilterOptions(GetTradeFilterOptionsDTO $queryDTO): array
    {
        $result = $this->tradeRepository->fetchAll(
            $queryDTO->page,
            $queryDTO->pageSize,
            $queryDTO->sortBy,
            $queryDTO->sortOrder,
            $queryDTO->searchTerm
        );

        $responseDTOs = [];

        foreach ($result as $trade) {
            $responseDTOs[] = TradeFilterOptionsResponseDTO::fromEntity($trade);
        }

        return $responseDTOs;
    }
}

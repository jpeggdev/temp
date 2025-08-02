<?php

namespace App\Services\ChartFilterOption;

use App\DTO\Query\FilterOption\GetYearFilterOptionsDTO;
use App\DTO\Response\YearFilterOptionsResponseDTO;
use App\Repository\InvoiceRepository;
use Doctrine\DBAL\Exception;

readonly class GetYearFilterOptionsService
{
    public function __construct(
        private InvoiceRepository $invoiceRepository
    ) {
    }

    /**
     * @throws Exception
     */
    public function getFilterOptions(GetYearFilterOptionsDTO $queryDTO): array
    {
        $result = $this->invoiceRepository->fetchInvoicedAtYears(
            $queryDTO->intacctId,
            $queryDTO->page,
            $queryDTO->pageSize,
            $queryDTO->sortBy,
            $queryDTO->sortOrder,
            $queryDTO->searchTerm,
        )->toArray();

        $responseDTOs = [];
        $startIndex = ($queryDTO->page - 1) * $queryDTO->pageSize;

        foreach (array_values($result) as $index => $year) {
            $syntheticId = $startIndex + $index + 1;
            $responseDTOs[] = new YearFilterOptionsResponseDTO($syntheticId, $year);
        }

        return $responseDTOs;
    }
}

<?php

namespace App\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesByYearAndMonthDataException;
use App\Repository\CompanyRepository;
use App\Repository\StochasticDashboardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

readonly class TotalSalesByYearAndMonthDataService
{
    public const MONTH_NUMBER_TO_MONTH_NAME_MAP = [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];

    public function __construct(
        private LoggerInterface $logger,
        protected CompanyRepository $companyRepository,
        protected StochasticDashboardRepository $stochasticDashboardRepository,
    ) {
    }

    /**
     * @throws FailedToGetTotalSalesByYearAndMonthDataException
     */
    public function getData(StochasticDashboardDTO $dto): array
    {
        $company = $this->companyRepository->findOneByIdentifier($dto->intacctId);
        if (!$company) {
            return [];
        }

        try {
            $chartData = $this->stochasticDashboardRepository->fetchTotalSalesByYearAndMonthData($dto);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new FailedToGetTotalSalesByYearAndMonthDataException();
        }

        return $this->formatChartData($chartData, $dto);
    }

    /**
     * Formats sales data by month and year.
     * Uses selected years or defaults to the last 7.
     * Missing years in the range are filled with zeros.
     */
    private function formatChartData(
        ArrayCollection $data,
        StochasticDashboardDTO $dto
    ): array {
        $formattedData = [];

        if (!empty($dto->years)) {
            $years = array_map('intval', $dto->years);
        } else {
            $currentYear = (int)date('Y');
            $years = range($currentYear - 6, $currentYear);
        }

        foreach ($data as $record) {
            $year = (int)$record['year'];

            if (empty($dto->years) && !in_array($year, $years, true)) {
                continue;
            }

            $month = (int)$record['month'];
            $totalSales = (int)$record['total_sales'];
            $monthName = self::MONTH_NUMBER_TO_MONTH_NAME_MAP[$month];

            $formattedData[$monthName]['month'] ??= $monthName;
            $formattedData[$monthName][$year] = $totalSales;
        }

        if (empty($dto->years)) {
            foreach ($formattedData as &$formattedDataItem) {
                foreach ($years as $year) {
                    $formattedDataItem[$year] ??= 0;
                }

                // Sort with "month" first
                uksort($formattedDataItem, static function ($a, $b) {
                    if ($a === 'month') {
                        return -1;
                    }

                    if ($b === 'month') {
                        return 1;
                    }

                    return $a <=> $b;
                });
            }
            unset($formattedDataItem);
        }

        return array_values($formattedData);
    }
}

<?php

namespace App\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetCustomersAverageInvoiceComparisonChartDataException;
use App\Repository\CompanyRepository;
use App\Repository\StochasticDashboardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

readonly class CustomersAverageInvoiceComparisonDataService
{
    public function __construct(
        private LoggerInterface $logger,
        private CompanyRepository $companyRepository,
        private StochasticDashboardRepository $chartRepository,
    ) {
    }

    /**
     * @throws FailedToGetCustomersAverageInvoiceComparisonChartDataException
     */
    public function getData(StochasticDashboardDTO $dto): array
    {
        $company = $this->companyRepository->findOneByIdentifier($dto->intacctId);
        if (!$company) {
            return [];
        }

        try {
            $chartData = $this->chartRepository->getCustomersAverageInvoiceComparisonChartData($dto);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new FailedToGetCustomersAverageInvoiceComparisonChartDataException();
        }

        return $this->formatChartData($chartData);
    }

    private function formatChartData(ArrayCollection $data): array
    {
        $totalNew = 0.0;
        $totalRepeat = 0.0;
        $count = count($data);

        foreach ($data as $chartDataItem) {
            $totalNew += (float)$chartDataItem['newCustomerAvg'];
            $totalRepeat += (float)$chartDataItem['repeatCustomerAvg'];
        }

        $avgNew = $count > 0 ? (int)($totalNew / $count) : 0;
        $avgRepeat = $count > 0 ? (int)($totalRepeat / $count) : 0;

        return [
            'chartData' => $data,
            'avgSales' => [
                'newCustomerAvg' => $avgNew,
                'repeatCustomerAvg' => $avgRepeat,
            ],
        ];
    }
}

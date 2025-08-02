<?php

namespace App\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesByZipCodeDataException;
use App\Repository\CompanyRepository;
use App\Repository\StochasticDashboardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

readonly class TotalSalesByZipCodeDataService
{
    public function __construct(
        private LoggerInterface $logger,
        private CompanyRepository $companyRepository,
        private StochasticDashboardRepository $chartRepository,
    ) {
    }

    /**
     * @throws FailedToGetTotalSalesByZipCodeDataException
     */
    public function getData(StochasticDashboardDTO $dto): array
    {
        if (!$this->validateChartDTO($dto)) {
            return [];
        }

        try {
            $chartData = $this->chartRepository->fetchTotalSalesByZipCodeChartData(
                $dto
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new FailedToGetTotalSalesByZipCodeDataException();
        }

        return $this->formatChartData($chartData)->toArray();

    }

    private function validateChartDTO(StochasticDashboardDTO $dto): bool
    {
        return (bool) $this->companyRepository->findOneByIdentifier($dto->intacctId);
    }

    private function formatChartData(ArrayCollection $data): ArrayCollection
    {
        return $data->map(function ($chartDataItem) {
            return [
                'postalCode' => (string)$chartDataItem['postalCode'],
                'totalSales' => (int)$chartDataItem['totalSales'],
            ];
        });
    }
}

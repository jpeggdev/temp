<?php

namespace App\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToLifetimeValueDataException;
use App\Repository\CompanyRepository;
use App\Repository\StochasticDashboardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

readonly class LifetimeValueDataService
{
    public function __construct(
        private LoggerInterface $logger,
        private CompanyRepository $companyRepository,
        private StochasticDashboardRepository $chartRepository,
    ) {
    }

    /**
     * @throws FailedToLifetimeValueDataException
     */
    public function getData(StochasticDashboardDTO $dto): array
    {
        if (!$this->validateChartDTO($dto)) {
            return [];
        }

        try {
            $chartData = $this->chartRepository->fetchLifetimeValueChartData($dto);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new FailedToLifetimeValueDataException();
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
                'salesPeriod' => (string)$chartDataItem['salesPeriod'],
                'totalSales' => (int)$chartDataItem['totalSales'],
                'salesPercentage' => (int)round($chartDataItem['salesPercentage']),
            ];
        });
    }
}

<?php

namespace App\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToLifetimeValueDataException;
use App\Repository\CompanyRepository;
use App\Repository\StochasticDashboardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

readonly class LifetimeValueByTierDataService
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
            $chartData = $this->chartRepository->fetchLifetimeValueByTierChartData($dto);
            return $this->formatChartData($chartData);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new FailedToLifetimeValueDataException();
        }
    }

    private function validateChartDTO(StochasticDashboardDTO $dto): bool
    {
        return (bool) $this->companyRepository->findOneByIdentifier($dto->intacctId);
    }

    private function formatChartData(ArrayCollection $data): array
    {
        $formattedChartData = [];
        $totalHouseholdsCount = 0;

        foreach ($data as $chartDataItem) {
            $formattedChartData[] = [
                'tier' => (string)$chartDataItem['tier'],
                'householdCount' => (int)$chartDataItem['householdCount'],
                'totalSales' => (int)$chartDataItem['totalSales'],
            ];

            $totalHouseholdsCount += (int)$chartDataItem['householdCount'];
        }

        return [
            'chartData' => $formattedChartData,
            'totalHouseholdsCount' => $totalHouseholdsCount,
        ];
    }
}

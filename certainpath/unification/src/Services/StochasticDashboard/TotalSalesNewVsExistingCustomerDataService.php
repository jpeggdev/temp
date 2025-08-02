<?php

namespace App\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesNewVsExistingCustomerDataException;
use App\Repository\CompanyRepository;
use App\Repository\StochasticDashboardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

readonly class TotalSalesNewVsExistingCustomerDataService
{
    public function __construct(
        private LoggerInterface $logger,
        private CompanyRepository $companyRepository,
        private StochasticDashboardRepository $chartRepository,
    ) {
    }

    /**
     * @throws FailedToGetTotalSalesNewVsExistingCustomerDataException
     */
    public function getData(StochasticDashboardDTO $dto): array
    {
        $company = $this->companyRepository->findOneByIdentifier($dto->intacctId);
        if (!$company) {
            return [];
        }

        try {
            $chartData = $this->chartRepository->fetchTotalSalesNewVsExistingCustomerChartData($dto);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new FailedToGetTotalSalesNewVsExistingCustomerDataException();
        }

        return $this->formatChartData($chartData);
    }

    private function formatChartData(ArrayCollection $data): array
    {
        $formattedData = [];

        foreach ($data as $dataItem) {
            $formattedData[] = [
                'HF' => (int)($dataItem['hf']),
                'NC' => (int)($dataItem['nc']),
                'total' => (int)($dataItem['hf']) + (int)($dataItem['nc']),
                'year' => (int)$dataItem['year'],
            ];
        }

        return $formattedData;
    }
}

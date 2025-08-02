<?php

namespace App\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetPercentageOfNewCustomersByZipCodeDataException;
use App\Repository\CompanyRepository;
use App\Repository\StochasticDashboardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

readonly class PercentageOfNewCustomersByZipCodeDataService
{
    public function __construct(
        private LoggerInterface $logger,
        private CompanyRepository $companyRepository,
        private StochasticDashboardRepository $chartRepository,
    ) {
    }

    /**
     * @throws FailedToGetPercentageOfNewCustomersByZipCodeDataException
     */
    public function getData(StochasticDashboardDTO $dto): array
    {
        $company = $this->companyRepository->findOneByIdentifier($dto->intacctId);
        if (!$company) {
            return [];
        }

        try {
            $chartData = $this->chartRepository->getPercentageOfNewCustomersByZipCodeChartData($dto);
            return $this->formatChartData($chartData);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new FailedToGetPercentageOfNewCustomersByZipCodeDataException();
        }
    }

    private function formatChartData(ArrayCollection $data): array
    {
        $grouped = [];

        foreach ($data as $item) {
            $postalCode = $item['postalCode'];
            $year = (string) $item['year'];
            $percentage = $item['percentage'];

            if (!isset($grouped[$postalCode])) {
                $grouped[$postalCode] = ['postalCode' => $postalCode];
            }

            $grouped[$postalCode][$year] = $percentage;
        }

        return array_values($grouped);
    }
}

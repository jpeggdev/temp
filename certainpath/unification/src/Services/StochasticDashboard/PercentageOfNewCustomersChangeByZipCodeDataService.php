<?php

namespace App\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetPercentageOfNewCustomersChangeByZipCodeDataException;
use App\Repository\CompanyRepository;
use App\Repository\StochasticDashboardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

readonly class PercentageOfNewCustomersChangeByZipCodeDataService
{
    public function __construct(
        private LoggerInterface $logger,
        private CompanyRepository $companyRepository,
        private StochasticDashboardRepository $chartRepository,
    ) {
    }

    /**
     * @throws FailedToGetPercentageOfNewCustomersChangeByZipCodeDataException
     */
    public function getData(StochasticDashboardDTO $dto): array
    {
        $company = $this->companyRepository->findOneByIdentifier($dto->intacctId);
        if (!$company) {
            return [];
        }

        try {
            $data = $this->chartRepository->getPercentageOfNewCustomersByZipCodeTableData($dto);
            return $this->formatData($data);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new FailedToGetPercentageOfNewCustomersChangeByZipCodeDataException();
        }
    }

    private function formatData(ArrayCollection $data): array
    {
        $grouped = [];

        foreach ($data as $item) {
            $postalCode = $item['postalCode'];
            $year = (string) $item['year'];
            $ncCount = (int) ($item['nc_count'] ?? 0);
            $percentChange = isset($item['percent_change']) ? (float) $item['percent_change'] : 0.00;

            if (!isset($grouped[$postalCode])) {
                $grouped[$postalCode] = ['postalCode' => $postalCode];
            }

            $grouped[$postalCode][$year] = [
                'ncCount' => $ncCount,
                'percentChange' => $percentChange,
            ];
        }

        return array_values($grouped);
    }
}

<?php

namespace App\Services;

use App\Entity\Company;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

readonly class CustomerMetricsService
{
    private const CUSTOMER_BATCH_SIZE = 1000;

    public function __construct(
        private CustomerRepository $customerRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }
    public function updateCustomerMetricsForCompany(
        Company $company
    ): void {
        $customerIds = $this->customerRepository->getAllCustomerIdsForCompany(
            $company
        );
        $this->logger->info(
            'Updating customer metrics for company',
            [
                'count' => count($customerIds),
                'company' => $company->getIdentifier()
            ]
        );
        $customerIdBatches = array_chunk(
            $customerIds,
            self::CUSTOMER_BATCH_SIZE
        );
        foreach ($customerIdBatches as $customerIds) {
            $this->updateCustomerMetricsForCustomerIdBatch(
                $company,
                $customerIds
            );
        }
    }

    private function updateCustomerMetricsForCustomerIdBatch(
        Company $company,
        array $customerIds
    ): void {
        $this->logger->info(
            'Updating Batch: customer metrics for company',
            [
                'count' => count($customerIds),
                'company' => $company->getIdentifier()
            ]
        );
        $customers = $this->customerRepository->findCustomersById(
            $customerIds
        );
        $count = 0;
        foreach ($customers as $customer) {
            $count++;
            $customer->updateCustomerMetrics();
            $this->customerRepository->persist($customer);
            if ($count % 100 === 0) {
                $this->logger->info(
                    'Updating Batch: customer metrics for company',
                    [
                        'count' => $count,
                        'company' => $company->getIdentifier()
                    ]
                );
            }
        }
        $this->flushAndClearEntityManager();
    }

    /**
     * @return void
     */
    private function flushAndClearEntityManager(): void
    {
        $this->logger->info(
            'Flushing and clearing entity manager'
        );
        $this->customerRepository->flush();
        $this->entityManager->clear();
    }
}

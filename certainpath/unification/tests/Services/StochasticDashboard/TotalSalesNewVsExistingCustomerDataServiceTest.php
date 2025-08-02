<?php

namespace App\Tests\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\Trade;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesNewVsExistingCustomerDataException;
use App\Services\StochasticDashboard\TotalSalesNewVsExistingCustomerDataService;
use App\Tests\FunctionalTestCase;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Random\RandomException;

class TotalSalesNewVsExistingCustomerDataServiceTest extends FunctionalTestCase
{
    private Company $company;
    private ArrayCollection $invoices;
    private TotalSalesNewVsExistingCustomerDataService $dataService;

    /**
     * @throws Exception
     * @throws RandomException
     * @throws \DateMalformedStringException
     */
    public function setUp(): void
    {
        parent::setUp();

        $trade = $this->getTradeRepository()->findByName(Trade::ELECTRICAL);
        $this->company = $this->initializeCompany();
        $prospects = $this->initializeProspects($this->company);
        $customers = $this->initializeCustomers($prospects);
        $this->invoices = $this->initializeInvoices($customers, $trade);

        $this->dataService = $this->getService(TotalSalesNewVsExistingCustomerDataService::class);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws FailedToGetTotalSalesNewVsExistingCustomerDataException
     */
    public function testGetChartData(): void
    {
        $dto = $this->prepareStochasticDashboardDTO($this->company);

        $actualData = $this->dataService->getData($dto);
        $expectedData = $this->prepareExpectedData($this->invoices);

        foreach ($expectedData as $key => $expectedDataItem) {
            $this->assertArrayHasKey($key, $actualData);
            $chartDataItem = $actualData[$key];

            $this->assertArrayHasKey('NC', $chartDataItem);
            $this->assertArrayHasKey('HF', $chartDataItem);
            $this->assertArrayHasKey('year', $chartDataItem);
            $this->assertArrayHasKey('total', $chartDataItem);

            $this->assertEquals($chartDataItem['HF'], $expectedDataItem['HF']);
            $this->assertEquals($chartDataItem['NC'], $expectedDataItem['NC']);
            $this->assertEquals($chartDataItem['year'], $expectedDataItem['year']);
            $this->assertEquals($chartDataItem['total'], $expectedDataItem['total']);
        }
    }

    /**
     * @throws \Exception
     * @throws \DateMalformedStringException
     */
    private function prepareExpectedData(ArrayCollection $invoices): array
    {
        $expectedData = [];

        /** @var Invoice $invoice */
        foreach ($invoices as $invoice) {
            $invoicedAt = $invoice->getInvoicedAt();
            $invoiceYear = (int) $invoicedAt->format('Y');
            $firstTransactionDate = $this->getFirstTransactionDate($invoice->getCustomer());

            $hf = 0;
            $nc = 0;

            if ($firstTransactionDate) {
                $firstTransactionYear = (int)$firstTransactionDate->format('Y');

                if ($firstTransactionYear === $invoiceYear) {
                    $nc = $invoice->getTotal();
                }

                if ($firstTransactionYear < $invoiceYear) {
                    $hf = $invoice->getTotal();
                }
            }

            if (isset($expectedData[$invoiceYear])) {
                $expectedData[$invoiceYear]['HF'] += $hf;
                $expectedData[$invoiceYear]['NC'] += $nc;
            } else {
                $expectedData[$invoiceYear] = [
                    'HF' => $hf,
                    'NC' => $nc,
                    'year' => $invoiceYear,
                ];
            }
        }

        foreach ($expectedData as $year => $data) {
            $expectedData[$year]['HF'] = (int)$data['HF'];
            $expectedData[$year]['NC'] = (int)$data['NC'];
            $expectedData[$year]['total'] = $expectedData[$year]['HF'] + $expectedData[$year]['NC'];
        }

        ksort($expectedData);

        return array_values($expectedData);
    }

    /**
     * @throws \Exception
     */
    private function getFirstTransactionDate(Customer $customer): ?DateTimeImmutable
    {
        $invoiceRepo = $this->getInvoiceRepository();
        $firstInvoice = $invoiceRepo->createQueryBuilder('i')
            ->select('MIN(i.invoicedAt) AS firstTransactionDate')
            ->where('i.customer = :customer')
            ->setParameter('customer', $customer)
            ->getQuery()
            ->getSingleResult();

        $firstTransactionDate = $firstInvoice['firstTransactionDate'] ?? null;

        if ($firstTransactionDate) {
            return new DateTimeImmutable($firstTransactionDate);
        }

        return null;
    }

    private function prepareStochasticDashboardDTO(
        Company $company
    ): StochasticDashboardDTO {
        return new StochasticDashboardDTO(
            intacctId: $company->getIdentifier()
        );
    }
}

<?php

namespace App\Tests\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Entity\Company;
use App\Entity\Trade;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesByYearAndMonthDataException;
use App\Services\StochasticDashboard\TotalSalesByYearAndMonthDataService;
use App\Tests\FunctionalTestCase;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Random\RandomException;

class TotalSalesByYearAndMonthDataServiceTest extends FunctionalTestCase
{
    private Company $company;
    private ArrayCollection $invoices;
    private TotalSalesByYearAndMonthDataService $chartService;

    /**
     * @throws Exception
     * @throws RandomException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->company = $this->initializeCompany();
        $this->initializeTestData($this->company);

        $this->chartService = $this->getService(TotalSalesByYearAndMonthDataService::class);
    }

    /**
     * @throws FailedToGetTotalSalesByYearAndMonthDataException
     */
    public function testGetChartData(): void
    {
        $dto = $this->prepareStochasticDashboardDTO($this->company);
        $actualData = $this->chartService->getData($dto);

        $expectedData = $this->prepareExpectedData($this->invoices);
        $years = $this->getInvoiceYears($expectedData);

        foreach ($expectedData as $key => $expectedDataItem) {
            $this->assertNotEmpty($actualData[$key]);
            $chartDataItem = $actualData[$key];

            $this->assertArrayHasKey('month', $chartDataItem);

            foreach ($years as $year) {
                $this->assertArrayHasKey($year, $chartDataItem);
                $this->assertEquals($expectedDataItem[$year], $chartDataItem[$year]);
            }
        }
    }

    private function prepareStochasticDashboardDTO(
        Company $company
    ): StochasticDashboardDTO {
        return new StochasticDashboardDTO(
            intacctId: $company->getIdentifier()
        );
    }

    private function prepareExpectedData(ArrayCollection $invoices): array
    {
        $expectedData = [];
        $monthNumberToMonthNameMap = TotalSalesByYearAndMonthDataService::MONTH_NUMBER_TO_MONTH_NAME_MAP;

        foreach ($monthNumberToMonthNameMap as $monthName) {
            $expectedData[$monthName] = ['month' => $monthName];
        }

        $years = [];
        foreach ($invoices as $invoice) {
            $year = (int)$invoice->getInvoicedAt()->format('Y');
            $years[$year] = true;
        }

        foreach ($expectedData as &$monthData) {
            foreach ($years as $year => $_) {
                $monthData[(string)$year] = 0.0;
            }
        }
        unset($monthData);

        foreach ($invoices as $invoice) {
            $invoicedAt = $invoice->getInvoicedAt();
            $year = (int)$invoicedAt->format('Y');
            $month = (int)$invoicedAt->format('n');
            $monthName = $monthNumberToMonthNameMap[$month];

            $expectedData[$monthName][(string)$year] += (float)$invoice->getTotal();
        }

        foreach ($expectedData as &$monthData) {
            foreach ($monthData as $key => $totalSales) {
                if ($key !== 'month') {
                    $monthData[$key] = (int)$totalSales;
                }
            }
        }
        unset($monthData);

        return array_values($expectedData);
    }

    /**
     * @throws RandomException
     */
    public function initializeTestData(Company $company): void
    {
        $currentYear = (int) date('Y');
        $previousYear = $currentYear - 1;

        $invoiceDateJanCurrentYear = new DateTimeImmutable("$currentYear-01-15");
        $invoiceDateFebPreviousYear = new DateTimeImmutable("$previousYear-02-20");

        $address1 = $this->initializeAddress(
            company: $company,
            city: 'Dallas',
            postalCode: 11111
        );
        $address2 = $this->initializeAddress(
            company: $company,
            city: 'Austin',
            postalCode: 22222
        );

        $prospect1 = $this->initializeProspect($company, $address1);
        $prospect2 = $this->initializeProspect($company, $address2);

        $customer1 = $this->initializeCustomer($prospect1);
        $customer2 = $this->initializeCustomer($prospect2);

        $tradeElectrical = $this->getTradeRepository()->findByName(Trade::ELECTRICAL);

        $this->initializeInvoice(
            customer: $customer1,
            trade: $tradeElectrical,
            total: 100.0,
            invoiceDate: $invoiceDateFebPreviousYear
        );
        $this->initializeInvoice(
            customer: $customer1,
            trade: $tradeElectrical,
            total: 33.0,
            invoiceDate: $invoiceDateJanCurrentYear
        );

        $this->initializeInvoice(
            customer: $customer2,
            trade: $tradeElectrical,
            total: 100.0,
            invoiceDate: $invoiceDateFebPreviousYear
        );
        $this->initializeInvoice(
            customer: $customer2,
            trade: $tradeElectrical,
            total: 50.0,
            invoiceDate: $invoiceDateJanCurrentYear
        );

        $this->company = $company;

        $this->invoices = new ArrayCollection(
            $this->getInvoiceRepository()->findBy(['company' => $company])
        );
    }

    private function getInvoiceYears(array $data): array
    {
        $dataItem = array_keys($data[0]);
        return array_filter($dataItem, static fn($key) => $key !== 'month');
    }
}

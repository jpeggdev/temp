<?php

namespace App\Tests\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Entity\Company;
use App\Entity\Trade;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetCustomersAverageInvoiceComparisonChartDataException;
use App\Services\StochasticDashboard\CustomersAverageInvoiceComparisonDataService;
use App\Tests\FunctionalTestCase;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Random\RandomException;

class CustomersAverageInvoiceComparisonDataServiceTest extends FunctionalTestCase
{
    private Company $company;
    private Trade $tradeElectrical;
    private Trade $tradeHVAC;
    private CustomersAverageInvoiceComparisonDataService $dataService;

    /**
     * @throws Exception
     * @throws RandomException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->company = $this->initializeCompany();
        $this->dataService = $this->getService(CustomersAverageInvoiceComparisonDataService::class);
        $this->tradeElectrical = $this->getTradeRepository()->findByName(Trade::ELECTRICAL);
        $this->tradeHVAC = $this->getTradeRepository()->findByName(Trade::HVAC);

        $this->initializeTestData($this->company);
    }

    /**
     * @throws FailedToGetCustomersAverageInvoiceComparisonChartDataException
     */
    public function testGetData(): void
    {
        $dto = $this->prepareStochasticDashboardDTO($this->company);
        $actualData = $this->dataService->getData($dto);
        $expectedData = $this->prepareExpectedData();

        $this->assertArrayHasKey('chartData', $actualData);
        $this->assertArrayHasKey('avgSales', $actualData);

        $expectedChartData = $expectedData['chartData'];
        $expectedAvgSales = $expectedData['avgSales'];

        $actualChartData = $actualData['chartData'];
        $actualAvgSales = $actualData['avgSales'];

        $this->assertCount(count($expectedChartData), $actualChartData);

        foreach ($expectedChartData as $expectedYearData) {
            $year = $expectedYearData['year'];

            $actualYearDataEntries = array_filter(
                $actualChartData->toArray(),
                static fn ($entry) => $entry['year'] === $year
            );

            $this->assertNotEmpty($actualYearDataEntries, "No data found for year $year");

            $actualYearData = array_values($actualYearDataEntries)[0];

            $this->assertSame(
                $expectedYearData['newCustomerAvg'],
                $actualYearData['newCustomerAvg'],
                "Mismatch in newCustomerAvg for year $year"
            );

            $this->assertSame(
                $expectedYearData['repeatCustomerAvg'],
                $actualYearData['repeatCustomerAvg'],
                "Mismatch in repeatCustomerAvg for year $year"
            );
        }

        $this->assertSame(
            $expectedAvgSales['newCustomerAvg'],
            $actualAvgSales['newCustomerAvg'],
            "Mismatch in overall newCustomerAvg"
        );

        $this->assertSame(
            $expectedAvgSales['repeatCustomerAvg'],
            $actualAvgSales['repeatCustomerAvg'],
            "Mismatch in overall repeatCustomerAvg"
        );
    }


    private function prepareStochasticDashboardDTO(
        Company $company
    ): StochasticDashboardDTO {
        return new StochasticDashboardDTO(
            intacctId: $company->getIdentifier(),
            trades: [$this->tradeElectrical->getId(), $this->tradeHVAC->getId()],
            years: [2017, 2018, 2019],
            cities: ['Dallas', 'Austin', 'Houston']

        );
    }

    private function prepareExpectedData(): array
    {
        return [
            'chartData' => [
                [
                    'year' => 2017,
                    'newCustomerAvg' => 5185,
                    'repeatCustomerAvg' => null,
                ],
                [
                    'year' => 2018,
                    'newCustomerAvg' => 31000,
                    'repeatCustomerAvg' => 2400,
                ],
            ],
            'avgSales' => [
                'newCustomerAvg' => 18092,
                'repeatCustomerAvg' => 1200,
            ],
        ];
    }

    /**
     * @throws RandomException
     */
    public function initializeTestData(Company $company): void
    {
        $invoiceDate2017 = new DateTimeImmutable('2017-01-01');
        $invoiceDate2018 = new DateTimeImmutable('2018-01-01');

        $prospect1Address = $this->initializeAddress(
            company: $company,
            city: 'Dallas',
            postalCode: 11111
        );
        $prospect2Address = $this->initializeAddress(
            company: $company,
            city:'Dallas',
            postalCode: 11111
        );
        $prospect3Address = $this->initializeAddress(
            company: $company,
            city:'Houston',
            postalCode: 22222
        );

        $prospect1 = $this->initializeProspect(
            $company,
            $prospect1Address
        );
        $prospect2 = $this->initializeProspect(
            $company,
            $prospect2Address
        );
        $prospect3 = $this->initializeProspect(
            $company,
            $prospect3Address
        );

        $customer1 = $this->initializeCustomer($prospect1);
        $customer2 = $this->initializeCustomer($prospect2);
        $customer3 = $this->initializeCustomer($prospect3);

        $this->initializeInvoice(
            customer: $customer1,
            trade: $this->tradeElectrical,
            total: 370.1,
            invoiceDate: $invoiceDate2017
        );
        $this->initializeInvoice(
            customer: $customer1,
            trade: $this->tradeElectrical,
            total: 2400.0,
            invoiceDate: $invoiceDate2018
        );
        $this->initializeInvoice(
            customer: $customer2,
            trade: $this->tradeElectrical,
            total: 10000.0,
            invoiceDate: $invoiceDate2017
        );
        $this->initializeInvoice(
            customer: $customer3,
            trade: $this->tradeHVAC,
            total: 31000.0,
            invoiceDate: $invoiceDate2018
        );
    }
}

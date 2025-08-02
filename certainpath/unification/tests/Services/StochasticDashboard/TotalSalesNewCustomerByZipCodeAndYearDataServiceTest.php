<?php

namespace App\Tests\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Entity\Company;
use App\Entity\Trade;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesNewVsExistingCustomerDataException;
use App\Services\StochasticDashboard\TotalSalesNewCustomerByZipCodeAndYearDataService;
use App\Tests\FunctionalTestCase;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Random\RandomException;

class TotalSalesNewCustomerByZipCodeAndYearDataServiceTest extends FunctionalTestCase
{
    private Company $company;
    private Trade $tradeHVAC;
    private Trade $tradeElectrical;
    private TotalSalesNewCustomerByZipCodeAndYearDataService $dataService;

    /**
     * @throws Exception
     * @throws RandomException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->company = $this->initializeCompany();
        $this->dataService = $this->getService(TotalSalesNewCustomerByZipCodeAndYearDataService::class);
        $this->tradeElectrical = $this->getTradeRepository()->findByName(Trade::ELECTRICAL);
        $this->tradeHVAC = $this->getTradeRepository()->findByName(Trade::HVAC);

        $this->initializeTestData($this->company);
    }

    /**
     * @throws FailedToGetTotalSalesNewVsExistingCustomerDataException
     */
    public function testGetData(): void
    {
        $dto = $this->prepareStochasticDashboardDTO($this->company);
        $actualData = $this->dataService->getData($dto);
        $expectedData = $this->prepareExpectedData();

        $expected = [];
        foreach ($expectedData as $row) {
            $expected[$row['postalCode']][$row['year']] = $row['sales'];
        }

        $actual = [];
        foreach ($actualData as $row) {
            $actual[$row['postalCode']][$row['year']] = $row['sales'];
        }

        $expectedCount = count($expectedData);
        $actualCount = 0;
        foreach ($actual as $years) {
            $actualCount += count($years);
        }
        $this->assertSame($expectedCount, $actualCount);

        foreach ($expected as $postalCode => $years) {
            $this->assertArrayHasKey($postalCode, $actual);

            foreach ($years as $year => $expectedTotalSales) {
                $this->assertArrayHasKey($year, $actual[$postalCode]);
                $this->assertSame($expectedTotalSales, $actual[$postalCode][$year]);
            }
        }

        $this->assertArrayNotHasKey('2019', $actual['11111'] ?? []);

        $this->assertArrayNotHasKey('33333', $actual);
    }



    private function prepareStochasticDashboardDTO(
        Company $company
    ): StochasticDashboardDTO {
        return new StochasticDashboardDTO(
            intacctId: $company->getIdentifier(),
            trades: [$this->tradeElectrical->getId()],
            years: [2017, 2018],
            cities: ['Dallas', 'Austin', 'Houston']

        );
    }

    private function prepareExpectedData(): array
    {
        return [
            [
                'postalCode' => "11111",
                'year' => "2017",
                'sales' => 370.1
            ],
            [
                'postalCode' => "22222",
                'year' => "2017",
                'sales' => 10000.0,
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
        $invoiceDate2019 = new DateTimeImmutable('2019-01-01');

        $prospect1Address = $this->initializeAddress(
            company: $company,
            city: 'Dallas',
            postalCode: 11111
        );
        $prospect2Address = $this->initializeAddress(
            company: $company,
            city:'Austin',
            postalCode: 22222
        );
        $prospect3Address = $this->initializeAddress(
            company: $company,
            city:'Houston',
            postalCode: 33333
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
            invoiceDate: $invoiceDate2019
        );
        $this->initializeInvoice(
            customer: $customer2,
            trade: $this->tradeElectrical,
            total: 10000.0,
            invoiceDate: $invoiceDate2017
        );
        $this->initializeInvoice(
            customer: $customer2,
            trade: $this->tradeElectrical,
            total: 10000.0,
            invoiceDate: $invoiceDate2018
        );
        $this->initializeInvoice(
            customer: $customer3,
            trade: $this->tradeHVAC,
            total: 31000.0,
            invoiceDate: $invoiceDate2017
        );
    }
}

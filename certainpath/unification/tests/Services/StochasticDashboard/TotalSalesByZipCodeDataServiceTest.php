<?php

namespace App\Tests\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Entity\Company;
use App\Entity\Trade;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetTotalSalesByZipCodeDataException;
use App\Services\StochasticDashboard\TotalSalesByZipCodeDataService;
use App\Tests\FunctionalTestCase;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Random\RandomException;

class TotalSalesByZipCodeDataServiceTest extends FunctionalTestCase
{
    private Company $company;
    private Trade $tradeElectrical;
    private Trade $tradeHVAC;
    private TotalSalesByZipCodeDataService $dataService;

    /**
     * @throws Exception
     * @throws RandomException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->company = $this->initializeCompany();
        $this->dataService = $this->getService(TotalSalesByZipCodeDataService::class);
        $this->tradeElectrical = $this->getTradeRepository()->findByName(Trade::ELECTRICAL);
        $this->tradeHVAC = $this->getTradeRepository()->findByName(Trade::HVAC);

        $this->initializeTestData($this->company);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws FailedToGetTotalSalesByZipCodeDataException
     */
    public function testGetData(): void
    {
        $dto = $this->prepareStochasticDashboardDTO($this->company);
        $actualData = $this->dataService->getData($dto);
        $expectedData = $this->prepareExpectedData();

        // Convert both arrays to associative keyed by postalCode
        $expected = [];
        foreach ($expectedData as $row) {
            $expected[$row['postalCode']] = $row['totalSales'];
        }

        $actual = [];
        foreach ($actualData as $row) {
            $actual[$row['postalCode']] = $row['totalSales'];
        }

        $this->assertCount(count($expected), $actual);

        foreach ($expected as $postalCode => $expectedTotalSales) {
            $this->assertArrayHasKey($postalCode, $actual);
            $this->assertSame($expectedTotalSales, $actual[$postalCode]);
        }

        $zipCodes = array_keys($actual);
        $this->assertNotContains('33333', $zipCodes);
        $this->assertNotContains('44444', $zipCodes);
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

    /**
     * @throws \DateMalformedStringException
     */
    private function prepareExpectedData(): array
    {
        return [
            [
                'postalCode' => "11111",
                'totalSales' => 370
            ],
            [
                'postalCode' => "22222",
                'totalSales' => 20000,
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
        $prospect4Address = $this->initializeAddress(
            company: $company,
            city:'San Antonio',
            postalCode: 44444
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
        $prospect4 = $this->initializeProspect(
            $company,
            $prospect4Address
        );

        $customer1 = $this->initializeCustomer($prospect1);
        $customer2 = $this->initializeCustomer($prospect2);
        $customer3 = $this->initializeCustomer($prospect3);
        $customer4 = $this->initializeCustomer($prospect4);

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
        $this->initializeInvoice(
            customer: $customer4,
            trade: $this->tradeElectrical,
            total: 4560.0,
            invoiceDate: $invoiceDate2017
        );
    }
}

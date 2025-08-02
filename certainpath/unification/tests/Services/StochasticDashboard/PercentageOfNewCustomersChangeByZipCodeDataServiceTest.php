<?php

namespace App\Tests\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Entity\Company;
use App\Entity\Trade;
use App\Exceptions\DomainException\StochasticDashboard\FailedToGetPercentageOfNewCustomersChangeByZipCodeDataException;
use App\Services\StochasticDashboard\PercentageOfNewCustomersChangeByZipCodeDataService;
use App\Tests\FunctionalTestCase;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Random\RandomException;

class PercentageOfNewCustomersChangeByZipCodeDataServiceTest extends FunctionalTestCase
{
    private Company $company;
    private Trade $tradeHvac;
    private Trade $tradeElectrical;
    private PercentageOfNewCustomersChangeByZipCodeDataService $dataService;

    /**
     * @throws Exception
     * @throws RandomException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->company = $this->initializeCompany();
        $this->dataService = $this->getPercentageOfNewCustomersByZipCodeTableService();
        $this->tradeHvac = $this->getTradeRepository()->findByName(Trade::HVAC);
        $this->tradeElectrical = $this->getTradeRepository()->findByName(Trade::ELECTRICAL);

        $this->initializeTestData($this->company);
    }

    /**
     * @throws FailedToGetPercentageOfNewCustomersChangeByZipCodeDataException
     */
    public function testGetData(): void
    {
        $expectedData = $this->prepareExpectedData();
        $dto = $this->prepareStochasticDashboardDTO($this->company);
        $percentageONewCustomersChageByZipCodeData = $this->dataService->getData($dto);

        $this->assertNotEmpty($percentageONewCustomersChageByZipCodeData);

        $dataZipCode11111 = $percentageONewCustomersChageByZipCodeData[0];
        $this->assertArrayHasKey('postalCode', $dataZipCode11111);
        $this->assertEquals('11111', $dataZipCode11111['postalCode']);

        foreach ([2017, 2018] as $year) {
            $this->assertArrayHasKey(
                $year,
                $dataZipCode11111
            );
            $this->assertArrayHasKey(
                'ncCount',
                $dataZipCode11111[$year]
            );
            $this->assertArrayHasKey(
                'percentChange',
                $dataZipCode11111[$year]
            );
            $this->assertEquals(
                $expectedData[0][$year]['ncCount'],
                $dataZipCode11111[$year]['ncCount']
            );
            $this->assertEquals(
                $expectedData[0][$year]['percentChange'],
                $dataZipCode11111[$year]['percentChange']
            );
        }

        $dataZipCode22222 = $percentageONewCustomersChageByZipCodeData[1];
        $this->assertArrayHasKey('postalCode', $dataZipCode22222);
        $this->assertEquals('22222', $dataZipCode22222['postalCode']);

        foreach ([2017, 2018] as $year) {
            $this->assertArrayHasKey(
                $year,
                $dataZipCode22222
            );
            $this->assertArrayHasKey(
                'ncCount',
                $dataZipCode22222[$year]
            );
            $this->assertArrayHasKey(
                'percentChange',
                $dataZipCode22222[$year]
            );
            $this->assertEquals(
                $expectedData[1][$year]['ncCount'],
                $dataZipCode22222[$year]['ncCount']
            );
            $this->assertEqualsWithDelta(
                $expectedData[1][$year]['percentChange'],
                $dataZipCode22222[$year]['percentChange'], 0.01
            );
        }

        $this->assertCount(count($expectedData), $percentageONewCustomersChageByZipCodeData);
    }

    private function prepareStochasticDashboardDTO(
        Company $company
    ): StochasticDashboardDTO {
        return new StochasticDashboardDTO(
            intacctId: $company->getIdentifier(),
            trades: [$this->tradeElectrical->getId(), $this->tradeHvac->getId()],
            years: [2017, 2018],
            cities: ['Dallas', 'Austin', 'San Antonio'],
        );
    }

    private function prepareExpectedData(): array
    {
        return [
            [
                'postalCode' => '11111',
                '2017' => [
                    'ncCount' => 1,
                    'percentChange' => 0.0,
                ],
                '2018' => [
                    'ncCount' => 2,
                    'percentChange' => 100.0,
                ]
            ],
            [
                'postalCode' => '22222',
                '2017' => [
                    'ncCount' => 3,
                    'percentChange' => 0.0,
                ],
                '2018' => [
                    'ncCount' => 1,
                    'percentChange' => -66.67,
                ]
            ],
        ];
    }

    /**
     * @throws RandomException
     */
    public function initializeTestData(Company $company): void
    {
        $tradeElectrical = $this->getTradeRepository()->findByName(Trade::ELECTRICAL);
        $invoiceDate2017 = new DateTimeImmutable('2017-01-01');
        $invoiceDate2018 = new DateTimeImmutable('2018-01-01');

        $prospect1Address = $this->initializeAddress(
            company: $company,
            city: 'Dallas',
            postalCode: 11111,
        );
        $prospect2Address = $this->initializeAddress(
            company: $company,
            city:'Dallas',
            postalCode: 11111,
        );
        $prospect3Address = $this->initializeAddress(
            company: $company,
            city:'Dallas',
            postalCode: 11111,
        );
        $prospect4Address = $this->initializeAddress(
            company: $company,
            city: 'Houston',
            postalCode: 22222,
        );
        $prospect5Address = $this->initializeAddress(
            company: $company,
            city: 'Houston',
            postalCode: 22222,
        );
        $prospect6Address = $this->initializeAddress(
            company: $company,
            city: 'Houston',
            postalCode: 22222,
        );
        $prospect7Address = $this->initializeAddress(
            company: $company,
            city: 'Houston',
            postalCode: 22222,
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
        $prospect5 = $this->initializeProspect(
            $company,
            $prospect5Address
        );
        $prospect6 = $this->initializeProspect(
            $company,
            $prospect6Address
        );
        $prospect7 = $this->initializeProspect(
            $company,
            $prospect7Address
        );

        $customer1 = $this->initializeCustomer($prospect1);
        $customer2 = $this->initializeCustomer($prospect2);
        $customer3 = $this->initializeCustomer($prospect3);
        $customer4 = $this->initializeCustomer($prospect4);
        $customer5 = $this->initializeCustomer($prospect5);
        $customer6 = $this->initializeCustomer($prospect6);
        $customer7 = $this->initializeCustomer($prospect7);

        $this->initializeInvoice(
            customer: $customer1,
            trade: $this->tradeHvac,
            total: 270.1,
            invoiceDate: $invoiceDate2017
        );
        $this->initializeInvoice(
            customer: $customer1,
            trade: $this->tradeHvac,
            total: 3202.0,
            invoiceDate: $invoiceDate2018
        );
        $this->initializeInvoice(
            customer: $customer2,
            trade: $this->tradeHvac,
            total: 2400.0,
            invoiceDate: $invoiceDate2018
        );
        $this->initializeInvoice(
            customer: $customer3,
            trade: $this->tradeHvac,
            total: 10000.0,
            invoiceDate: $invoiceDate2018
        );
        $this->initializeInvoice(
            customer: $customer4,
            trade: $tradeElectrical,
            total: 17000.0,
            invoiceDate: $invoiceDate2017
        );
        $this->initializeInvoice(
            customer: $customer5,
            trade: $this->tradeHvac,
            total: 22000.0,
            invoiceDate: $invoiceDate2017
        );
        $this->initializeInvoice(
            customer: $customer6,
            trade: $this->tradeHvac,
            total: 22000.0,
            invoiceDate: $invoiceDate2017
        );
        $this->initializeInvoice(
            customer: $customer7,
            trade: $this->tradeHvac,
            total: 5500.0,
            invoiceDate: $invoiceDate2018
        );
    }
}

<?php

namespace App\Tests\Services\StochasticDashboard;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\Entity\Company;
use App\Entity\Trade;
use App\Exceptions\DomainException\StochasticDashboard\FailedToLifetimeValueDataException;
use App\Services\StochasticDashboard\LifetimeValueByTierDataService;
use App\Tests\FunctionalTestCase;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Random\RandomException;

class LifetimeValueByTierDataServiceTest extends FunctionalTestCase
{
    private Company $company;
    private Trade $tradeElectrical;
    private LifetimeValueByTierDataService $dataService;

    /**
     * @throws Exception
     * @throws RandomException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->company = $this->initializeCompany();
        $this->dataService = $this->getService(LifetimeValueByTierDataService::class);
        $this->tradeElectrical = $this->getTradeRepository()->findByName(Trade::ELECTRICAL);

        $this->initializeTestData($this->company);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws FailedToLifetimeValueDataException
     */
    public function testGetChartData(): void
    {
        $expectedData = $this->prepareExpectedData();
        $chartDTO = $this->prepareFilterableChartDTO($this->company);
        $chartData = $this->dataService->getData($chartDTO);

        $this->assertArrayHasKey('totalHouseholdsCount', $chartData);
        $this->assertEquals($expectedData['totalHouseholdsCount'], $chartData['totalHouseholdsCount']);

        $this->assertArrayHasKey('chartData', $chartData);
        foreach ($chartData['chartData'] as $key => $chartDataItem) {
            $this->assertArrayHasKey('tier', $chartDataItem);
            $this->assertEquals($expectedData['chartData'][$key]['tier'], $chartDataItem['tier']);

            if (
                $chartDataItem['tier'] === 'Up to $500' ||
                $chartDataItem['tier'] === 'Greater than $5,000'
            ) {
                $this->assertEquals(1, $chartDataItem['householdCount']);
            } else {
                $this->assertEquals(0, $chartDataItem['householdCount']);
            }
        }
    }

    private function prepareFilterableChartDTO(
        Company $company
    ): StochasticDashboardDTO {
        return new StochasticDashboardDTO(
            intacctId: $company->getIdentifier(),
            trades: [$this->tradeElectrical->getId()],
            years: [2017],
            cities: ['Dallas', 'Houston']

        );
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function prepareExpectedData(): array
    {
        return [
            'chartData' => [
                [
                    'tier' => 'Up to $500',
					'householdCount' => 1
                ],
                [
                    'tier' => 'Greater than $500',
                    'householdCount' => 0
                ],
                [
                    'tier' => 'Greater than $1,000',
                    'householdCount' => 0
                ],
                [
                    'tier' => 'Greater than $2,500',
                    'householdCount' => 0
                ],
                [
                    'tier' => 'Greater than $5,000',
                    'householdCount' => 0
                ],
                [
                    'tier' => 'Greater than $10,000',
                    'householdCount' => 0
                ],
                [
                    'tier' => 'Greater than $20,000',
                    'householdCount' => 0
                ],
                [
                    'tier' => 'Greater than $30,000',
                    'householdCount' => 1
                ]
            ],
            'totalHouseholdsCount' => 2,
        ];
    }

    /**
     * @throws RandomException
     */
    public function initializeTestData(Company $company): void
    {
        $tradeHVAC = $this->getTradeRepository()->findByName(Trade::HVAC);
        $invoiceDate2017 = new DateTimeImmutable('2017-01-01');
        $invoiceDate2018 = new DateTimeImmutable('2018-01-01');

        $prospect1Address = $this->initializeAddress(
            company: $company,
            city: 'Dallas'
        );
        $prospect2Address = $this->initializeAddress(
            company: $company,
            city:'Austin'
        );
        $prospect3Address = $this->initializeAddress(
            company: $company,
            city:'Houston'
        );
        $prospect4Address = $this->initializeAddress(
            company: $company,
            city: 'Dallas'
        );
        $prospect5Address = $this->initializeAddress(
            company: $company,
            city: 'Houston'
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

        $customer1 = $this->initializeCustomer($prospect1);
        $customer2 = $this->initializeCustomer($prospect2);
        $customer3 = $this->initializeCustomer($prospect3);
        $customer4 = $this->initializeCustomer($prospect4);
        $customer5 = $this->initializeCustomer($prospect5);

        $this->initializeInvoice(
            customer: $customer1,
            trade: $this->tradeElectrical,
            total: 270.1,
            invoiceDate: $invoiceDate2017
        );
        $this->initializeInvoice(
            customer: $customer2,
            trade: $this->tradeElectrical,
            total: 2400.0,
            invoiceDate: $invoiceDate2017
        );
        $this->initializeInvoice(
            customer: $customer3,
            trade: $this->tradeElectrical,
            total: 10000.0,
            invoiceDate: $invoiceDate2017
        );
        $this->initializeInvoice(
            customer: $customer4,
            trade: $tradeHVAC,
            total: 31000.0,
            invoiceDate: $invoiceDate2017
        );
        $this->initializeInvoice(
            customer: $customer5,
            trade: $this->tradeElectrical,
            total: 11000.0,
            invoiceDate: $invoiceDate2018
        );
    }
}

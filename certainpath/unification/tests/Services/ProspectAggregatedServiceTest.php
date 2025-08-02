<?php

namespace App\Tests\Services;

use App\DTO\Domain\ProspectFilterRulesDTO;
use App\Entity\Company;
use App\Entity\Prospect;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Services\ProspectAggregatedService;
use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;
use App\Tests\FunctionalTestCase;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;

class ProspectAggregatedServiceTest extends FunctionalTestCase
{
    private Company $company;

    private ProspectAggregatedService $prospectAggregatedService;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->initializeProspectFilterRules();
        $this->company = $this->initializeCompany();
        $this->prospectAggregatedService = $this->getProspectAggregatedDataService();
    }

    /**
     * @throws ORMException
     * @throws ProspectFilterRuleNotFoundException
     */
    public function testGetAggregatedProspects(): void
    {
        $dto = $this->prepareDTO($this->company);
        $filterableProspects = $this->initializeFilterableProspects($this->company);
        $prospectsAggregatedData = $this->prospectAggregatedService->getProspectsAggregatedData($dto);
        $prospectPostalCodes = $this->getProspectsPostalCodesShort($filterableProspects);

        $this->assertCount(count($filterableProspects), $prospectsAggregatedData);

        foreach ($prospectsAggregatedData as $dataItem) {
            $this->assertArrayHasKey('postalCode', $dataItem);
            $this->assertArrayHasKey('households', $dataItem);
            $this->assertArrayHasKey('avgSales', $dataItem);

            $this->assertContains($dataItem['postalCode'], $prospectPostalCodes);
            $this->assertEquals(1, $dataItem['households']);
            $this->assertEquals(0, $dataItem['avgSales']);
        }
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function testGetAggregatedProspectsFiltersProspectsWithSamePreferredAddress(): void
    {
        $dto = $this->prepareDTO($this->company);
        $this->initializeProspectsWithSameAddress($this->company);
        $prospectsAggregatedData = $this->prospectAggregatedService->getProspectsAggregatedData($dto);

        $this->assertCount(1, $prospectsAggregatedData);
        $this->assertEquals(1, $prospectsAggregatedData[0]['households']);
    }

    private function prepareDTO(Company $company): ProspectFilterRulesDTO
    {
        return new ProspectFilterRulesDTO(
            $company->getIdentifier(),
            ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_AND_CUSTOMERS_VALUE,
            ProspectFilterRuleRegistry::CUSTOMER_MAX_LTV_5000_VALUE,
            ProspectFilterRuleRegistry::EXCLUDE_CLUB_MEMBERS_VALUE,
            ProspectFilterRuleRegistry::EXCLUDE_CUSTOMER_INSTALLATIONS_VALUE,
            21,
            40,
            20,
            1
        );
    }

    /**
     * @throws ORMException
     */
    private function initializeFilterableProspects(Company $company): array
    {
        // Though it's not a customer, the prospect still matched due to
        // the ProspectFilterRuleRegistry::RULE_NAME_INCLUDE_PROSPECTS_AND_CUSTOMERS rule
        $address1 = $this->initializeAddress($company);
        $prospect1 = $this->initializeProspect($company, $address1);
        $this->initializeProspectDetails($prospect1, 39);

        // Included since matches all the criteria
        $address2 = $this->initializeAddress($company);
        $prospect2 = $this->initializeProspect($company, $address2);
        $this->initializeProspectDetails($prospect2, 33);
        $this->initializeCustomer($prospect2, lifetimeValue: '49.99');

        // Excluded due to hasSubscription = true AND hasInstallation = true
        $address3 = $this->initializeAddress($company);
        $prospect3 = $this->initializeProspect($company, $address3);
        $this->initializeProspectDetails($prospect3, 35);
        $this->initializeCustomer($prospect3, hasSubscription: true, hasInstallation: true, lifetimeValue: '49.99');

        // Excluded due to lifetime value > 5000 AND isPreferred = false
        $address4 = $this->initializeAddress($company);
        $prospect4 = $this->initializeProspect($company, $address4, isPreferred: false);
        $this->initializeProspectDetails($prospect4, 40);
        $this->initializeCustomer($prospect4, lifetimeValue: '5000.20');

        $this->entityManager->refresh($prospect1);
        $this->entityManager->refresh($prospect2);

        return [$prospect1, $prospect2];
    }

    private function initializeProspectsWithSameAddress(Company $company): void
    {
        $address1 = $this->initializeAddress($company);

        $prospect1 = $this->initializeProspect($company, $address1);
        $prospect2 = $this->initializeProspect($company, $address1);

        $this->initializeProspectDetails($prospect1, 39);
        $this->initializeProspectDetails($prospect2, 38);
    }

    private function getProspectsPostalCodesShort(array $prospects): array
    {
        return array_map(static function ($prospect) {
            /** @var Prospect $prospect */
            return $prospect->getPreferredAddress()?->getPostalCodeShort();
        }, $prospects);
    }
}

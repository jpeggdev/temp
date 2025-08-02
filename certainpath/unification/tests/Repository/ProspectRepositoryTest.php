<?php

namespace App\Tests\Repository;

use App\DTO\Domain\ProspectFilterRulesDTO;
use App\Entity\Company;
use App\Entity\Prospect;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;
use App\Tests\FunctionalTestCase;

class ProspectRepositoryTest extends FunctionalTestCase
{
    public function testGetProspectsForPostProcessing(): void
    {
        $repository = $this->getProspectRepository();
        $repository->save($this->getProspect());
        $query = $repository->getProspectsForPostProcessingQuery();
        $this->assertCount(1, $query->getResult());
    }

    private function getCompany(): Company
    {
        return $this->getCompanyRepository()->findOneBy([
            'identifier' => 'UNI1'
        ]);
    }

    private function getProspect(): Prospect
    {
        return (new Prospect())
            ->setFullName('First Last')
            ->setFirstName('First')
            ->setLastName('Last')
            ->setCity('New York')
            ->setState('NY')
            ->setPostalCode('00001')
            ->setCompany($this->getCompany())
            ->setAddress1('1234 Madeup Ln')
            ->setAddress2('Suite 100');
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function testFetchAllByProspectFilterRulesDTO(): void
    {
        $repository = $this->getProspectRepository();

        $this->initializeProspectFilterRules();
        $company = $this->initializeCompany();
        $this->initializeProspectsWithSameAddress($company);

        $prospectFilterRulesDTO = new ProspectFilterRulesDTO(
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

        $allProspects = $repository->fetchAllByCompanyId($company->getId());
        $this->assertCount(2, $allProspects);

        $prospectsWithSameAddress = $repository->fetchAllByProspectFilterRulesDTO($prospectFilterRulesDTO);
        $this->assertCount(1, $prospectsWithSameAddress);
        $this->assertEquals($allProspects->get(1)->getId(), $prospectsWithSameAddress->get(0)->getId());
    }

    private function initializeProspectsWithSameAddress(Company $company): void
    {
        $address1 = $this->initializeAddress($company);

        $prospect1 = $this->initializeProspect($company, $address1);
        $prospect2 = $this->initializeProspect($company, $address1);

        $this->initializeProspectDetails($prospect1, 39);
        $this->initializeProspectDetails($prospect2, 38);
    }
}

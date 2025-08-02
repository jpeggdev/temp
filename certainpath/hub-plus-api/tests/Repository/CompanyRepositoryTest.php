<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Tests\AbstractKernelTestCase;

class CompanyRepositoryTest extends AbstractKernelTestCase
{
    public function testCreateCompany(): void
    {
        $companyName = $this->faker->company();
        $websiteUrl = $this->faker->url();
        $salesforceId = $this->faker->uuid();
        $intacctId = $this->faker->uuid();
        $companyEmail = $this->faker->email();

        $company = new Company();
        $company->setCompanyName($companyName);
        $company->setWebsiteUrl($websiteUrl);
        $company->setSalesforceId($salesforceId);
        $company->setIntacctId($intacctId);
        $company->setCompanyEmail($companyEmail);

        $this->companyRepository->save($company, true);

        $retrievedCompany = $this->companyRepository->findOneByIdentifier(
            $intacctId
        );
        self::assertNotNull($retrievedCompany);
        $this->assertNotNull($retrievedCompany->getId());
        $this->assertSame($companyName, $retrievedCompany->getCompanyName());
        $this->assertSame($websiteUrl, $retrievedCompany->getWebsiteUrl());
        $this->assertSame($salesforceId, $retrievedCompany->getSalesforceId());
        $this->assertSame($intacctId, $retrievedCompany->getIntacctId());
        $this->assertSame($companyEmail, $retrievedCompany->getCompanyEmail());
    }
}

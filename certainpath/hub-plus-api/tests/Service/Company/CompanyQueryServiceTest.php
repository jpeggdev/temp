<?php

namespace App\Tests\Service\Company;

use App\DTO\Request\Company\CompanyQueryDTO;
use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Service\Company\CompanyQueryService;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class CompanyQueryServiceTest extends TestCase
{
    private CompanyQueryService $service;

    private MockInterface $companyRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->companyRepository = \Mockery::mock(CompanyRepository::class);
        $this->service = new CompanyQueryService($this->companyRepository);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        \Mockery::close();
    }

    public function testGetCompanies(): void
    {
        // Create a CompanyQueryDTO for testing
        $queryDto = new CompanyQueryDTO();
        $queryDto->page = 1;
        $queryDto->pageSize = 10;
        $queryDto->sortBy = 'companyName';
        $queryDto->sortOrder = 'ASC';
        $queryDto->searchTerm = 'Test';

        // Create mock company
        $company = \Mockery::mock(Company::class);
        $company->shouldReceive('getId')->andReturn(1);
        $company->shouldReceive('getCompanyName')->andReturn('Test Company');
        $company->shouldReceive('getUuid')->andReturn('uuid-value');
        $company->shouldReceive('getSalesforceId')->andReturn('sf-id');
        $company->shouldReceive('getIntacctId')->andReturn('intacct-id');
        $company->shouldReceive('isMarketingEnabled')->andReturn(true);
        $company->shouldReceive('isCertainPath')->andReturn(false);
        $company->shouldReceive('getCreatedAt')->andReturn(new \DateTime());
        $company->shouldReceive('getUpdatedAt')->andReturn(new \DateTime());

        // Update repository expectations to match service implementation
        $this->companyRepository->shouldReceive('findCompaniesByQuery')
            ->with($queryDto)  // Remove extra parameter
            ->andReturn([$company]);

        $this->companyRepository->shouldReceive('getTotalCount')
            ->with($queryDto)  // Remove extra parameter
            ->andReturn(1);

        // Execute service method
        $result = $this->service->getCompanies($queryDto);

        // Assertions
        $this->assertIsArray($result);
        $this->assertArrayHasKey('companies', $result);
        $this->assertArrayHasKey('totalCount', $result);
        $this->assertCount(1, $result['companies']);
        $this->assertEquals(1, $result['totalCount']);

        // Add assertions for company data
        $resultCompany = $result['companies'][0];
        $this->assertEquals(1, $resultCompany->id);
        $this->assertEquals('Test Company', $resultCompany->companyName);
        $this->assertEquals('uuid-value', $resultCompany->uuid);
        $this->assertEquals('sf-id', $resultCompany->salesforceId);
        $this->assertEquals('intacct-id', $resultCompany->intacctId);
        $this->assertTrue($resultCompany->marketingEnabled);
    }
}
